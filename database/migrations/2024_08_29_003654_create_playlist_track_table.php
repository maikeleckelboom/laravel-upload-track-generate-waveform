<?php

use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('playlist_track', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('track_number')->nullable();
            $table->foreignIdFor(Playlist::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Track::class)->constrained()->cascadeOnDelete();
            $table->unique(['playlist_id', 'track_id']);
            $table->index('playlist_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_track');
    }
};
