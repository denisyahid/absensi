<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Pns;
use App\Models\RujukanModel;
use App\Models\RujukanUser;

class RujukanEdit extends Component
{
    use WithFileUploads;

    public $rujukan_id;
    public $rujukan;

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
    public $existing_bukti_rujukan;
    public $existing_kuitansi_bensin;

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

    public function mount($id)
    {
        $this->rujukan_id = $id;
        $this->rujukan = RujukanModel::with('users')->findOrFail($id);

        // Load data rujukan
        $this->nomor_surat = $this->rujukan->nomor_surat;
        $this->perihal = $this->rujukan->perihal;
        $this->waktu = $this->rujukan->waktu;
        $this->tempat = $this->rujukan->tempat;
        $this->biaya_perdin = $this->rujukan->biaya_perdin;
        $this->alat_angkut = $this->rujukan->alat_angkut;
        $this->tanggal_berangkat = $this->rujukan->tanggal_berangkat;
        $this->tanggal_kembali = $this->rujukan->tanggal_kembali;
        $this->alamat_rujukan = $this->rujukan->alamat_rujukan;
        $this->nama_rujukan = $this->rujukan->nama_rujukan;

        // Existing file paths
        $this->existing_bukti_rujukan = $this->rujukan->bukti_rujukan;
        $this->existing_kuitansi_bensin = $this->rujukan->kuitansi_bensin;

        // Load peserta
        $this->selectedUsers = $this->rujukan->users->pluck('id')->toArray();
        $this->peserta = RujukanUser::where('rujukan_id', $this->rujukan_id)->get(['user_id', 'pns_id'])->map(function ($item) {
            return [
                'user_id'   => $item->user_id,
                'pns_id'    => $item->pns_id,
                'searchPns' => '',
                'user_name' => User::find($item->user_id)->name,
            ];
        })->toArray();

//         $this->syncPeserta();
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
        // Ambil data peserta dari pivot
        $existingPeserta = [];

        foreach ($this->rujukan->users as $user) {
            $pivotData = $user->pivot;
            $existingPeserta[$user->id] = [
                'user_id'   => $user->id,
                'pns_id'    => $pivotData->pns_id,
                'searchPns' => '',
                'user_name' => $user->name,
            ];
        }

        // Gabungkan dengan selectedUsers yang baru
        $this->peserta = collect($this->selectedUsers)->map(function ($userId) use ($existingPeserta) {
            if (isset($existingPeserta[$userId])) {
                return $existingPeserta[$userId];
            }

            return [
                'user_id'   => $userId,
                'pns_id'    => null,
                'searchPns' => '',
                'user_name' => User::find($userId)->name,
            ];
        })->values()->toArray();
    }

    public function update()
    {
        $this->validate();

        $pesertaClean = collect($this->peserta)
            ->map(function ($p) {
                return [
                    'user_id' => (int) $p['user_id'],
                    'pns_id'  => empty($p['pns_id']) ? null : (int) $p['pns_id'],
                ];
            })
            ->toArray();

        // Handle file uploads
        $buktiPath = $this->bukti_rujukan
            ? $this->bukti_rujukan->store('rujukan', 'public')
            : $this->existing_bukti_rujukan;

        $kuitansiPath = $this->kuitansi_bensin
            ? $this->kuitansi_bensin->store('rujukan', 'public')
            : $this->existing_kuitansi_bensin;

        // Update rujukan
        $this->rujukan->update([
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
            'kuitansi_bensin' => $kuitansiPath,
        ]);

        // Update peserta
        $syncData = [];
        foreach ($pesertaClean as $peserta) {
            $syncData[$peserta['user_id']] = ['pns_id' => $peserta['pns_id']];
        }
        if (!empty($syncData)) {
            $this->rujukan->users()->sync($syncData);
        }

        session()->flash('success', 'Rujukan berhasil diperbarui');
        return redirect()->to('/rujukan');
    }

    public function delete()
    {
        // Hapus file jika ada
        if ($this->rujukan->bukti_rujukan && file_exists(storage_path('app/public/' . $this->rujukan->bukti_rujukan))) {
            unlink(storage_path('app/public/' . $this->rujukan->bukti_rujukan));
        }

        if ($this->rujukan->kuitansi_bensin && file_exists(storage_path('app/public/' . $this->rujukan->kuitansi_bensin))) {
            unlink(storage_path('app/public/' . $this->rujukan->kuitansi_bensin));
        }

        $this->rujukan->delete();

        session()->flash('success', 'Rujukan berhasil dihapus');
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

                ->get();
        }

        $userOptions = $this->userOption;

        return view('rujukan-edit', compact('users', 'pnsOptions', 'userOptions'));
    }
}
