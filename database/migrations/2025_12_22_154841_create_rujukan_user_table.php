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
        Schema::create('rujukan_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rujukan_id')
                ->constrained('rujukan_models')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
             $table->foreignId('pns_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rujukan_user');
    }
};
