<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('apk_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apk_id')->constrained('project_apks')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('device_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('download_time');
            $table->timestamps(); // adds created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apk_downloads');
    }
};
