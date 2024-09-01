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
            $table->string('codec_tag_string')->comment('The codec tag (mp3, m4a, aac)');
            $table->unsignedBigInteger('duration_ts')->comment('Duration in milliseconds');
            $table->unsignedInteger('sample_rate')->comment('Sample rate in Hz');
            $table->unsignedInteger('bit_rate')->comment('Bit rate in kbps');
            $table->unsignedInteger('bits_per_sample')->nullable()->comment('Bit depth (8, 16, 24, 32)');
            $table->unsignedInteger('channels')->comment('Number of channels');
            $table->string('language')->nullable()->comment('The language of the spoken content');
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
