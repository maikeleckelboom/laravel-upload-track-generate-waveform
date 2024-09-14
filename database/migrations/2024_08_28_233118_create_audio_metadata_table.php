<?php

use App\Models\Track;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audio_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('codec_name')->comment('The codec used to encode the audio');
            $table->unsignedInteger('duration')->comment('Duration in seconds');
            $table->unsignedInteger('sample_rate')->comment('The rate of capture and playback');
            $table->unsignedInteger('bit_rate')->comment('The number of bits transmitted per second');
            $table->unsignedInteger('bits_per_sample')->nullable()->comment('Bit depth or sample size (8, 16, 24, 32)');
            $table->unsignedInteger('channels')->comment('Number of channels');
            $table->foreignIdFor(Track::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_metadata');
    }
};
