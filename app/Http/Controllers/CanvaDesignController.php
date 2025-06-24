<?php

namespace App\Http\Controllers;

use App\Models\CanvaDesign;
use App\Models\CanvaToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

class CanvaDesignController extends Controller
{



    // TO CANVA

    public function redirectToCanva(Request $request)
    {
        // 1) App credentials
        $clientId     = env('CANVA_CLIENT_ID');   // or env('CANVA_CLIENT_ID')
        $redirectUri  = route('canva.callback');              // e.g. http://127.0.0.1:8000/canva/callback

        // 2) PKCE: create code_verifier (43-128 random chars) & code_challenge
        $codeVerifier  = Str::random(128);
        session(['canva_pkce_verifier' => $codeVerifier]);

        $codeChallenge = rtrim(
            strtr(
                base64_encode(hash('sha256', $codeVerifier, true)),
                '+/',
                '-_'
            ),
            '='
        );

        // 3) CSRF protection (high-entropy random string)
        $state = Str::uuid()->toString();
        session(['canva_oauth_state' => $state]);

        // 4) Build the authorization URL
        $query = http_build_query([
            'response_type'         => 'code',
            'client_id'             => $clientId,
            'redirect_uri'          => $redirectUri,
            'scope'                 => 'design:meta:read design:content:read',
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 's256',
            'state'                 => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect("https://www.canva.com/api/oauth/authorize?$query");
        // return redirect("https://www.canva.com/api/oauth/authorize?code_challenge_method=s256&response_type=code&client_id=OC-AZeL0Im9q8Yr&redirect_uri=http%3A%2F%2F127.0.0.1%3A8000%2Fcanva%2Fcallback&scope=design:meta:read%20design:content:read&code_challenge=<CODE_CHALLENGE>");
    }





    public function handleCanvaCallback(Request $request)
    {
        // 1. CSRF protection
        if ($request->input('state') !== session('canva_oauth_state')) {
            abort(403, 'Invalid state parameter');
        }

        // 2. Prepare credentials
        $clientId     = env('CANVA_CLIENT_ID');     // or env('CANVA_CLIENT_ID')
        $clientSecret = env('CANVA_CLIENT_SECRET'); // or env('CANVA_CLIENT_SECRET')
        $redirectUri  = route('canva.callback');
        $code         = $request->input('code');
        $verifier     = session('canva_pkce_verifier');

        // 3. Send POST request with Basic Auth
        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post('https://api.canva.com/rest/v1/oauth/token', [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $redirectUri,
                'code_verifier' => $verifier,
            ]);

        // 4. Handle response
        $data = $response->json();
        if ($response->failed()) {
            dd('OAuth Error:', $data); // or handle it gracefully
        }


        CanvaToken::updateOrCreate([], [
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at'    => now()->addSeconds($data['expires_in'] - 0),
        ]);

        // (Session copy is optional now)
        // 5. Store token or use as needed
        session(['canva_access_token' => $data['access_token']]);
        return redirect()->route('canva.index')->with('success', 'Successfully authenticated with Canva!');
    }













    public function index()
    {
        $designs = CanvaDesign::orderByDesc('created_at')->get();
        return view('canva.index', compact('designs'));
    }

    public function create()
    {
        return view('canva.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:canva_designs,name',
            'canva_link' => 'required|url',
            'expiry_date' => 'required|date|after:today',
        ]);

        $download_link = Str::uuid();

        // Store in DB
        $design = CanvaDesign::create([
            'name' => $request->name,
            'canva_link' => $request->canva_link,
            'download_link' => $download_link,
            'expiry_date' => $request->expiry_date,
        ]);

        // Fetch and store PDF/image
        $this->fetchAndStorePdf($design); // <-- call function below

        return redirect()->route('canva.index')->with('success', 'Design added!');
    }



    public function webhook(Request $request)
    {



        Log::info('Canva webhook hit:', $request->all());

        $canva_link = $request->input('canva_link');

        if (!$canva_link) {
            return response()->json(['status' => 'error', 'message' => 'Missing canva_link'], 400);
        }

        $design = CanvaDesign::where('canva_link', $canva_link)->first();
        if ($design) {
            $this->fetchAndStorePdf($design); // This method should already download the latest version
            Log::info('Design updated: ' . $design->id);
        } else {
            Log::warning('No matching design for: ' . $canva_link);
        }

        return response()->json(['status' => 'ok']);
    }




    public function fetchAndStorePdf(CanvaDesign $design)
    {

        $accessToken = canvaAccessToken();
        if (!$accessToken) {
            return response()->json([
                'error' => 'Invalid Access Token',
            ]);
            Log::error('No valid Canva token.');
        }

        // 1️⃣ design_id from share link
        if (!preg_match('/design\/([^\/\?]+)/', $design->canva_link, $m)) {
            Log::error('Invalid Canva link: ' . $design->canva_link);
            return;
        }
        $designId = $m[1];
        // 2️⃣ Create export job
        $create = Http::withToken($accessToken)
            ->post('https://api.canva.com/rest/v1/exports', [
                'design_id' => $designId,
                'format'    => [
                    'type' => 'pdf'            // you can add size/pages here
                ],
            ]);

        if ($create->failed()) {
            Log::error('Export job creation failed: ' . $create->body());
            return;
        }

        $jobId = $create->json('job.id');
        if (!$jobId) {
            Log::error('Export job ID missing: ' . $create->body());
            return;
        }

        // 3️⃣ Poll until done (in_progress ➜ success/failed)
        $job     = null;
        $retries = 6;               // 6 × 5 s ≈ 30 s max wait
        while ($retries--) {
            Sleep::for(5)->seconds();

            $poll = Http::withToken($accessToken)
                ->get("https://api.canva.com/rest/v1/exports/{$jobId}");

            if ($poll->failed()) {
                Log::error('Polling failed: ' . $poll->body());
                return;
            }

            $job = $poll->json('job');
            if ($job['status'] === 'success' || $job['status'] === 'failed') {
                break;
            }
        }

        if (!$job || $job['status'] !== 'success') {
            Log::error('Export job did not complete: ' . json_encode($job));
            return;
        }

        // 4️⃣ Download first URL
        $downloadUrl = $job['urls'][0] ?? null;
        if (!$downloadUrl) {
            Log::error('No download URL returned.');
            return;
        }

        // Use slugified name for filename, fallback to download_link if empty
        $baseName = $design->name ? Str::slug($design->name) : $design->download_link;
        $relative = "canva_designs/{$baseName}.pdf";
        $absolute = Storage::disk('public')->path($relative);
        File::ensureDirectoryExists(dirname($absolute));

        $ok = Http::sink($absolute)->get($downloadUrl);
        if ($ok->failed()) {
            Log::error('PDF download failed: ' . $ok->status());
            return;
        }

        $design->update(['file_path' => $relative]);
        Log::info("PDF saved to {$relative}");
    }



    public function callback(Request $request)
    {
        $response = Http::asForm()->post('https://api.canva.com/auth/token', [
            'client_id' => env('CANVA_CLIENT_ID'),
            'client_secret' => env('CANVA_CLIENT_SECRET'),
            'code' => $request->input('code'),
            'redirect_uri' => 'YOUR_REDIRECT_URI',
            'grant_type' => 'authorization_code',
        ]);

        $accessToken = $response->json()['access_token'];

        // Use $accessToken to call Canva API and export a design as PDF
        $designId = 'DAFvmkT1gBk';
        $exportResponse = Http::withToken($accessToken)
            ->post("https://api.canva.com/v1/designs/{$designId}/exports", [
                'format' => 'pdf',
            ]);

        $pdfUrl = $exportResponse->json()['url'];

        $pdfContent = Http::get($pdfUrl)->body();
        Storage::disk('public')->put("canva_pdfs/{$designId}.pdf", $pdfContent);
    }




    // Helper to extract design ID from Canva link
    private function extractDesignId($canvaLink)
    {
        if (preg_match('/design\\/([A-Za-z0-9]+)/', $canvaLink, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function edit($id)
    {
        $design = CanvaDesign::findOrFail($id);
        return view('canva.edit', compact('design'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:canva_designs,name,' . $id . ',id',
            'canva_link' => 'required|url',
            'expiry_date' => 'required|date|after:today',
        ]);
        $design = CanvaDesign::findOrFail($id);

        $oldLink = $design->canva_link;
        $oldName = $design->name;
        $newName = $request->name;
        $newFileName = Str::slug($newName) . '.pdf';
        $newFilePath = 'canva_designs/' . $newFileName;

        $oldFilePath =  $design->file_path                     // Prefer column value
            ?: "canva_designs/{$design->name}.pdf";
        $design->name = $newName;
        $design->canva_link = $request->canva_link;
        $design->expiry_date = $request->expiry_date;
        $design->save();

        if ($request->canva_link == $oldLink) {
            // If the Canva link is the same, just rename the file if needed
            if ($oldName !== $newName && Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->move($oldFilePath, $newFilePath);
            }
        } else {

            // If the Canva link has changed, delete the old file and fetch the new PDF
            if (Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }
            $this->fetchAndStorePdf($design);
        }

        return redirect()->route('canva.index')->with('success', 'Design updated!');
    }

    public function destroy($id)
    {
        $design = CanvaDesign::findOrFail($id);
        $design->delete();
        return redirect()->route('canva.index')->with('success', 'Design deleted!');
    }
}
