<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CanvaDesign;
use App\Http\Controllers\CanvaDesignController;

class UpdateCanvaDesigns extends Command
{
    protected $signature = 'designs:update-canva';
    protected $description = 'Update all Canva designs to their latest PDF version';

    public function handle(): int
    {
        $this->info('Updating Canva designs...');

        $controller = app(CanvaDesignController::class);
        $designs = CanvaDesign::where('expiry_date', '>', now())->get();

        foreach ($designs as $design) {
            $this->line("Updating: " . $design->download_link);
            $controller->fetchAndStorePdf($design);
        }

        $this->info('✔ Done!');
        return Command::SUCCESS;
    }
}
