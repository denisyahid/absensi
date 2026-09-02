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
        Schema::table('rujukan_models', function (Blueprint $table) {
            $table->string('status')->nullable()->after('kuitansi_bensin');
            $table->string('menimbang')->nullable()->after('kuitansi_bensin');
            $table->string('dasar_surat')->nullable()->after('kuitansi_bensin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rujukan_models', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('menimbang');
            $table->dropColumn('dasar_surat');
        });
    }
};
