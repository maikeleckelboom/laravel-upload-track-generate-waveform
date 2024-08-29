<?php

use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('description')->nullable();

            $table->string('artist');

            $table->unsignedBigInteger('duration_ts')
                ->nullable()
                ->comment('Duration in milliseconds');

            $table->unsignedInteger('sample_rate')
                ->nullable()
                ->comment('Sample rate in Hz');

            $table->unsignedInteger('bit_depth')
                ->nullable()
                ->comment('Bits per sample');

            $table->unsignedInteger('channels')
                ->nullable()
                ->comment('Number of channels');

            $table->string('codec_name')
                ->nullable()
                ->comment('Codec used to encode the track');


            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Genre::class)->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
