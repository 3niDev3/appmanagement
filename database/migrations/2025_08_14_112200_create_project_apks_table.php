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
        Schema::create('project_apks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete(); // project relation
            $table->string('filename'); // APK file name
            $table->string('filepath'); // stored path
            $table->text('description')->nullable(); // optional description
            $table->unsignedBigInteger('uploaded_by')->nullable(); // user/admin who uploaded
            $table->unsignedBigInteger('download_count')->default(0); // total downloads
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_apks');
    }
};
