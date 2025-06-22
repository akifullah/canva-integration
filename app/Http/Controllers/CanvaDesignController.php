<?php

namespace App\Http\Controllers;

use App\Models\CanvaDesign;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


class CanvaDesignController extends Controller
{
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
            'canva_link' => 'required|url',
            'expiry_date' => 'required|date|after:today',
        ]);

        $download_link = Str::uuid();
        $design = CanvaDesign::create([
            'canva_link' => $request->canva_link,
            'download_link' => $download_link,
            'expiry_date' => $request->expiry_date,
        ]);

        // Fetch and store PDF (simulate for now)
        $this->fetchAndStorePdf($design);

        return redirect()->route('canva.index')->with('success', 'Design added!');
    }

    public function download($download_link)
    {
        $design = CanvaDesign::where('download_link', $download_link)->firstOrFail();
        $filePath = "canva_pdfs/{$download_link}.pdf";
        if (!Storage::disk('public')->exists($filePath)) {
            $this->fetchAndStorePdf($design);
        }
        return Storage::disk('public')->download($filePath, 'canva-design.pdf');
    }

    public function webhook(Request $request)
    {
        // Canva should send the design link or id
        $canva_link = $request->input('canva_link');
        $design = CanvaDesign::where('canva_link', $canva_link)->first();
        if ($design) {
            $this->fetchAndStorePdf($design);
        }
        return response()->json(['status' => 'ok']);
    }

    private function fetchAndStorePdf(CanvaDesign $design)
    {
        // Simulate fetching PDF from Canva
        // In real use, use Canva API to get PDF file from $design->canva_link
        $pdfContent = Http::get($design->canva_link)->body(); // Replace with real API call
        $filePath = "canva_pdfs/{$design->download_link}.pdf";
        Storage::disk('public')->put($filePath, $pdfContent);
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

    public function redirectToCanva(Request $request)
    {

        $clientId = env('CANVA_CLIENT_ID');
        $redirectUri = route('canva.callback');

        $verifier = Str::random(64);
        session(['pkce_verifier' => $verifier]);

        $challenge = rtrim(strtr(
            base64_encode(hash('sha256', $verifier, true)),
            '+/',
            '-_'
        ), '=');

        $url = 'https://www.canva.com/oauth?' . http_build_query([
            'client_id'             => $clientId,
            'redirect_uri'          => $redirectUri,
            'response_type'         => 'code',
            'scope'                 => 'openid design:content:read',
            'code_challenge_method' => 'S256',
            'code_challenge'        => $challenge,
        ]);

        return redirect($url);
    }

    public function handleCanvaCallback(Request $request)
    {
        $code = $request->input('code');
        $verifier = session('pkce_verifier'); // stored earlier

        if (!$verifier || !$code) {
            return response()->json(['error' => 'Missing verifier or code'], 400);
        }

        $response = Http::asForm()
            ->withBasicAuth(env('CANVA_CLIENT_ID'), env('CANVA_CLIENT_SECRET'))
            ->post('https://api.canva.com/rest/v1/oauth/token', [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'code_verifier' => $verifier,
                'redirect_uri'  => route('canva.callback'),
            ]);

        if ($response->failed()) {
            return response()->json($response->json(), 400);
        }

        $data = $response->json();

        // Example: store in session temporarily
        session([
            'canva_access_token'  => $data['access_token'],
            'canva_refresh_token' => $data['refresh_token'],
            'canva_expires_in'    => now()->addSeconds($data['expires_in']),
        ]);

        return view('canva.oauth-success', ['token' => $data['access_token']]);
    }

    // Helper to extract design ID from Canva link
    private function extractDesignId($canvaLink)
    {
        if (preg_match('/design\\/([A-Za-z0-9]+)/', $canvaLink, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
