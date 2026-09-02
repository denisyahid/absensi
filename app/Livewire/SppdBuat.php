<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Pns;
use App\Models\RujukanModel;

class SppdBuat extends Component
{
    use WithFileUploads;

    // Form utama
    public $nomor_surat;
    public $perihal = 'Rujukan Pasien';
    public $waktu;
    public $tempat;
    public $biaya_perdin = 70000;
    public $alat_angkut = 'roda empat';
    public $tanggal_berangkat;
    public $tanggal_kembali;
    public $alamat_rujukan;
    public $nama_rujukan;

    public $bukti_rujukan;
    public $kuitansi_bensin;

    // User & peserta
    public $search = '';
    public $selectedUsers = [];
    public $peserta = [];
    public $userOption = [];
    protected $rules = [
        'perihal' => 'required|min:6',
        'waktu' => 'required',
        'tempat' => 'required',
        'biaya_perdin' => 'required|numeric|min:0',
        'alat_angkut' => 'required',
        'tanggal_berangkat' => 'required|date',
        'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
        'alamat_rujukan' => 'required|min:6',
        'nama_rujukan' => 'required',
        'selectedUsers' => 'required|array|min:1',
        'selectedUsers.*' => 'exists:users,id',
        'peserta.*.pns_id' => 'nullable|exists:pns,id',
        'bukti_rujukan' => 'nullable|image|max:10240',
        'kuitansi_bensin' => 'nullable|image|max:10240',
    ];

    public function mount()
    {
        // $this->waktu = now()->format('H:i') . ' WIB s.d selesai';
        $this->waktu = '08.00 WIB s.d selesai';
        $this->tanggal_berangkat = now()->toDateString();
        $this->tanggal_kembali = now()->toDateString();

        // otomatis user login
//        $this->selectedUsers = [auth()->id()];
        // $this->syncPeserta();

        $this->nomor_surat = RujukanModel::generateNomorSurat();
    }

    /**
     * Sinkron peserta berdasarkan selectedUsers
     */
    public function updatedSelectedUsers()
    {
        $this->syncPeserta();
    }

    private function syncPeserta()
    {
        $existing = collect($this->peserta)->keyBy('user_id');

        $this->peserta = collect($this->selectedUsers)->map(function ($userId) use ($existing) {
            return $existing[$userId] ?? [
                'user_id'   => $userId,
                'pns_id'    => null,
                'searchPns' => '',
                'user_name' => User::find($userId)->name,
            ];
        })->values()->toArray();
    }

    public function simpan()
    {
        $data = $this->validate();

        $pesertaClean = collect($this->peserta)
            ->map(function ($p) {
                return [
                    'user_id' => (int) $p['user_id'],
                    'pns_id'  => empty($p['pns_id']) ? null : (int) $p['pns_id'],
                ];
            })
            ->toArray();

        if ($this->bukti_rujukan) {
            $data['bukti_rujukan'] = $this->bukti_rujukan->store('rujukan', 'public');
        }

        if ($this->kuitansi_bensin) {
            $data['kuitansi_bensin'] = $this->kuitansi_bensin->store('rujukan', 'public');
        }
        $rujukan = RujukanModel::create([
            'nomor_surat' => $this->nomor_surat,
            'perihal' => $this->perihal,
            'waktu' => $this->waktu,
            'tempat' => $this->tempat,
            'biaya_perdin' => $this->biaya_perdin,
            'alat_angkut' => $this->alat_angkut,
            'tanggal_berangkat' => $this->tanggal_berangkat,
            'tanggal_kembali' => $this->tanggal_kembali,
            'alamat_rujukan' => $this->alamat_rujukan,
            'nama_rujukan' => $this->nama_rujukan,
            'bukti_rujukan' => $data['bukti_rujukan'] ?? null,
            'kuitansi_bensin' => $data['kuitansi_bensin'] ?? null,
        ]);



        $rujukan->users()->sync($pesertaClean);





        session()->flash('success', 'Rujukan berhasil dibuat');
        return redirect()->to('/rujukan');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('jabatan', 'like', "%{$this->search}%");
            })
            ->paginate(3);

        // PNS options PER USER
        $pnsOptions = [];
        foreach ($this->peserta as $index => $p) {
            $pnsOptions[$index] = Pns::query()
                ->when($p['searchPns'], function ($q) use ($p) {
                    $q->where('nama', 'like', "%{$p['searchPns']}%")
                        ->orWhere('nip', 'like', "%{$p['searchPns']}%");
                })
                ->limit(10)
                ->get();
        }
        $userOptions = $this->userOption;
        return view('sppd-buat', compact('users', 'pnsOptions', 'userOptions'));
    }
}
