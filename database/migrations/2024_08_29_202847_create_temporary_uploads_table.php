<?php

use App\Enum\TemporaryUploadStatus;
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
        Schema::create('temporary_uploads', function (Blueprint $table) {
            $table->id();

            $table->string('identifier')
                ->unique()
                ->comment('Unique identifier provided by the client');

            $table->string('name')
                ->nullable()
                ->comment('The file name with extension');

            $table->string('type')
                ->nullable()
                ->comment('The file MIME type');

            $table->unsignedBigInteger('size')
                ->nullable()
                ->comment('The file size in bytes');

            $table->unsignedBigInteger('chunk_size')
                ->comment('Size of each chunk in bytes');

            $table->unsignedBigInteger('received_chunks')
                ->default(0)
                ->comment('Number of received chunks');

            $table->enum('status', TemporaryUploadStatus::toArray())
                ->default(TemporaryUploadStatus::QUEUED)
                ->comment('Enum representing the upload status');

            $table->json('meta')
                ->nullable()
                ->comment('Additional metadata provided by the client');

            $table->string('disk')
                ->default('local-temporary')
                ->comment('Disk to store the chunks');

            $table->string('chunks_disk')
                ->default('local-temporary')
                ->comment('Disk to store the chunks');

            $table->foreignIdFor(User::class)
                ->comment('User that initiated the upload');

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
