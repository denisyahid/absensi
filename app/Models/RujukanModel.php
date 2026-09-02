<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RujukanModel extends Model
{
    protected $guarded = ['id'];
    protected $with = ['users'];
    protected $casts = [
        'tanggal_berangkat' => 'date',
        'tanggal_pulang' => 'date',
    ];
    public function users()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'rujukan_user',
            'rujukan_id',
            'user_id'
        );
    }
    public function pns()
    {
        return $this->belongsToMany(
            \App\Models\Pns::class,
            'rujukan_user',
            'rujukan_id',
            'pns_id'
        );
    }

    public function peserta()
    {
        return $this->hasMany(RujukanUser::class, 'rujukan_id');
    }

    public static function generateNomorSurat()
    {
        $year = date('Y');

        // Mencari nomor terakhir dengan format yang sama untuk tahun ini
        $lastNomor = self::where('nomor_surat', 'like', "800.1.11.1/%/$year/RSUD_Malangbong")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastNomor && $lastNomor->nomor_surat) {
            // Ekstrak angka dari nomor surat terakhir
            preg_match('/800\.1\.11\.1\/\s*(\d+)\/' . $year . '\/RSUD_Malangbong/', $lastNomor->nomor_surat, $matches);

            if (isset($matches[1])) {
                $nextNumber = (int)$matches[1] + 1;
            } else {
                $nextNumber = 1;
            }
        } else {
            $nextNumber = 1;
        }

        // Format dengan leading zeros minimal 3 digit
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return "800.1.11.1/ {$formattedNumber}/$year/RSUD_Malangbong";
    }

    public function getLamaPerjalananAttribute()
    {
        if (!$this->tanggal_berangkat || !$this->tanggal_kembali) {
            return 0;
        }

        return Carbon::parse($this->tanggal_berangkat)
            ->diffInDays(Carbon::parse($this->tanggal_kembali)) + 1;
    }

    public function getBiayaPerdinRupiahAttribute()
    {
        return 'Rp ' . number_format($this->biaya_perdin, 0, ',', '.');
    }
    function terbilang($angka)
    {
        $angka = abs($angka);

        $huruf = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas'
        ];

        if ($angka < 12) {
            return $huruf[$angka];
        } elseif ($angka < 20) {
            return $this->terbilang($angka - 10) . ' belas';
        } elseif ($angka < 100) {
            return $this->terbilang(intval($angka / 10)) . ' puluh ' . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            return 'seratus ' . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return $this->terbilang(intval($angka / 100)) . ' ratus ' . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return 'seribu ' . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return $this->terbilang(intval($angka / 1000)) . ' ribu ' . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return $this->terbilang(intval($angka / 1000000)) . ' juta ' . $this->terbilang($angka % 1000000);
        }

        return 'angka terlalu besar';
    }

    public function getBiayaPerdinTerbilangAttribute()
    {
        $angka = $this->terbilang($this->biaya_perdin);
        return ucfirst($angka) . ' rupiah';
    }
}
