<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\RujukanModel;
use App\Models\User;
use App\Models\Pns;

class SppdTest extends Component
{
    use WithFileUploads;

    // Data utama
    public $perihal = 'Rujukan Pasien';
    public $waktu;
    public $tempat;
    public $tanggal_berangkat;
    public $tanggal_kembali;
    public $alamat_rujukan;
    public $nama_rujukan;
    public $bukti_rujukan;

    // Data peserta: array dengan format [user_id, pns_id?]
    public $peserta = [];

    // Pencarian
    public $searchUser = '';
    public $searchPns = '';

    // Default values
    public $biaya_perdin = 70000;
    public $alat_angkut = 'roda empat';

    public function mount()
    {
        $this->waktu = now()->format('H:i') . ' WIB s.d selesai';
        $this->tanggal_berangkat = now()->toDateString();
        $this->tanggal_kembali = now()->toDateString();

        // Tambahkan user login sebagai peserta pertama
        $this->addPeserta(auth()->id());
    }

    // Tambah peserta baru
    public function addPeserta($userId = null)
    {
        $this->peserta[] = [
            'user_id' => $userId,
            'pns_id' => null,
            'user_name' => $userId ? User::find($userId)->name : '',
        ];
    }

    // Hapus peserta
    public function removePeserta($index)
    {
        unset($this->peserta[$index]);
        $this->peserta = array_values($this->peserta);
    }

    // Simpan rujukan
    public function simpan()
    {
        // Validasi sederhana
        $this->validate([
            'perihal' => 'required',
            'tanggal_berangkat' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
            'alamat_rujukan' => 'required',
            'nama_rujukan' => 'required',
            'peserta' => 'required|array|min:1',
            'peserta.*.user_id' => 'required|exists:users,id',
            'peserta.*.pns_id' => 'nullable|exists:pns,id',
        ]);

        // Handle upload file
        $buktiPath = null;
        if ($this->bukti_rujukan) {
            $buktiPath = $this->bukti_rujukan->store('rujukan', 'public');
        }

        // Buat rujukan
        $rujukan = RujukanModel::create([
            'nomor_surat' => RujukanModel::generateNomor(),
            'perihal' => $this->perihal,
            'waktu' => $this->waktu,
            'tempat' => $this->tempat,
            'biaya_perdin' => $this->biaya_perdin,
            'alat_angkut' => $this->alat_angkut,
            'tanggal_berangkat' => $this->tanggal_berangkat,
            'tanggal_kembali' => $this->tanggal_kembali,
            'alamat_rujukan' => $this->alamat_rujukan,
            'nama_rujukan' => $this->nama_rujukan,
            'bukti_rujukan' => $buktiPath,
        ]);

        // Simpan setiap peserta dengan PNS yang dipinjam
        foreach ($this->peserta as $peserta) {
            $pnsData = null;
            if ($peserta['pns_id']) {
                $pns = Pns::find($peserta['pns_id']);
                $pnsData = $pns ? [
                    'nama' => $pns->nama,
                    'nip' => $pns->nip,
                    'pangkat' => $pns->pangkat,
                    'golongan' => $pns->golongan,
                    'jabatan' => $pns->jabatan,
                ] : null;
            }

            // Simpan ke pivot table
            $rujukan->rujukanUsers()->create([
                'user_id' => $peserta['user_id'],
                'pns_id' => $peserta['pns_id'],
                'pns_data' => $pnsData,
            ]);
        }

        session()->flash('success', 'Rujukan berhasil dibuat');
        return redirect()->route('rujukan.index');
    }

    // Ambil user untuk dropdown
    public function getUserOptions()
    {
        return User::query()
            ->when($this->searchUser, function ($query) {
                $query->where('name', 'like', "%{$this->searchUser}%")
                    ->orWhere('email', 'like', "%{$this->searchUser}%");
            })
            ->limit(10)
            ->get();
    }

    // Ambil PNS untuk dropdown
    public function getPnsOptions()
    {
        return Pns::query()
            ->when($this->searchPns, function ($query) {
                $query->where('nama', 'like', "%{$this->searchPns}%")
                    ->orWhere('nip', 'like', "%{$this->searchPns}%");
            })
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('sppd-test', [
            'userOptions' => $this->getUserOptions(),
            'pnsOptions' => $this->getPnsOptions(),
        ]);
    }
}
