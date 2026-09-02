<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rujukan_models', function (Blueprint $table) {
            $table->id();
            $table->string('nama_rujukan');
            $table->string('alamat_rujukan');
            $table->string('bukti_rujukan')->nullable();
            $table->string('kuitansi_bensin')->nullable();



            $table->string('perihal')->default('rujukan pasien ');
            $table->string('nomor_surat')->default('rujukan pasien ');
            $table->date('tanggal_surat')->default(DB::raw('CURRENT_DATE'));
            $table->string('waktu')->default('08.00 WIB s.d Selesai');
            $table->string('tempat')->nullable();
            $table->integer('biaya_perdin')->default(70000);
            $table->string('alat_angkut')->default('Roda Empat');
            $table->date('tanggal_berangkat')->default(DB::raw('CURRENT_DATE'));
            $table->date('tanggal_kembali')->default(DB::raw('CURRENT_DATE'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rujukan_models');
    }
};
