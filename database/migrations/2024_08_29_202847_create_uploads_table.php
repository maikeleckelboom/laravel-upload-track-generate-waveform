<?php

use App\Enum\UploadStatus;
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
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->unsignedBigInteger('chunk_size');
            $table->unsignedBigInteger('received_chunks');
            $table->string('disk')->default('original');
            $table->enum('status', UploadStatus::toArray())->default(UploadStatus::QUEUED);
            $table->unsignedBigInteger('elapsed_time')->default(0);
            $table->float('transfer_speed')->default(0);

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
