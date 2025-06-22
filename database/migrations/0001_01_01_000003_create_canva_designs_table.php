<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('canva_designs', function (Blueprint $table) {
            $table->id();
            $table->string('canva_link');
            $table->string('download_link')->unique();
            $table->date('expiry_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canva_designs');
    }
}; 