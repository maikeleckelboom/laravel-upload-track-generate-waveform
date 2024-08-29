<?php

use App\Enum\UploadStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('temporary_uploads', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('chunk_size')
                ->comment('Size of each chunk in bytes');

            $table->unsignedBigInteger('received_chunks')
                ->default(0)
                ->comment('Number of received chunks');

            $table->enum('status', UploadStatus::toArray())
                ->default(UploadStatus::PENDING)
                ->comment('Current status of the upload');

            $table->string('identifier')
                ->unique()
                ->comment('Unique identifier provided by the client');

            $table->json('meta')
                ->nullable()
                ->comment('Additional metadata provided by the client');

            $table->string('disk')
                ->default('local-temporary')
                ->comment('Disk to store the chunks');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_uploads');
    }
};
