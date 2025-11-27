<?php

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
        Schema::create('file_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // uploader
            $table->foreignId('file_object_sector_id')->nullable()->constrained()->cascadeOnDelete();
            $table->uuid('logical_key'); // identifica il gruppo di versioni
            $table->unsignedInteger('version')->default(1);
            $table->enum('type', ['file', 'folder']);
            $table->string('name');
            $table->string('uploaded_name');
            $table->string('document_type')->nullable(); // tipo business opzionale
            $table->string('mime_type', 191)->nullable(); // null per folder
            $table->unsignedBigInteger('file_size')->default(0); // 0 per folder
            $table->string('storage_path'); // path su S3 o storage driver
            $table->boolean('is_public')->default(false);
            $table->boolean('uploaded_by_system')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['logical_key', 'version']);
            $table->index(['type', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_objects');
    }
};
