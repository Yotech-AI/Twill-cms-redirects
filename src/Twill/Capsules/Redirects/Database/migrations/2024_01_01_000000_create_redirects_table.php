<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title')->nullable();
            $table->json('redirects')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
