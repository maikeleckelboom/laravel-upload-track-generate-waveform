<?php

use App\Models\Album;
use App\Models\Artist;
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

            $table->string('name');

            $table->float('duration')->nullable();
            $table->float('bpm')->nullable();
            $table->string('key')->nullable();

            $table->string('disk')->default('tracks');
            $table->foreignIdFor(Album::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Artist::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
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
