<?php

use App\Models\Track;
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
        Schema::create('cues', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('')->comment('Cue name');
            $table->string('description')->nullable();
            $table->float('start_time')->comment('Start time in seconds');
            $table->unsignedInteger('index')->comment('Cue index');
            $table->foreignIdFor(Track::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cues');
    }
};
