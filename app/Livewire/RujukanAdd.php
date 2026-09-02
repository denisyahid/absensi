<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\RujukanModel;


use App\Models\User;

class RujukanAdd extends Component
{
    use WithFileUploads;

    public $alamat_rujukan = '';
    public $bukti_rujukan;
    public $nama_rujukan = '';
    public $search = '';
    public $selectedUsers = [];

    protected $rules = [
        'alamat_rujukan' => 'required|min:6',
        'nama_rujukan'   => 'required',
        'selectedUsers'  => 'required|array|min:1',
        'selectedUsers.*' => 'exists:users,id',
        'bukti_rujukan'  => 'nullable|image|max:10240',
    ];

    public function mount()
    {


        // otomatis tambahkan user login
        $this->selectedUsers = [auth()->user()->id];
    }

    public function simpan()
    {
        $data = $this->validate();

        if ($this->bukti_rujukan) {
            $data['bukti_rujukan'] = $this->bukti_rujukan->store('rujukan', 'public');
        }

        $rujukan = RujukanModel::create([
            'alamat_rujukan' => $this->alamat_rujukan,
            'nama_rujukan'   => $this->nama_rujukan,
            'bukti_rujukan'  => $data['bukti_rujukan'] ?? null,
        ]);

        // attach banyak user
        $rujukan->users()->sync($this->selectedUsers);

        session()->flash('success', 'Rujukan berhasil dibuat untuk beberapa user');

        return redirect()->to('/rujukan');
    }

    public function updateSelectedUsers()
    {
        $this->render();
    }

    public function render()
    {
        $users = User::query();

        if ($this->search) {
            $users->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('jabatan', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedUsers)) {
            $ids = implode(',', $this->selectedUsers);

            $users->orderByRaw("FIELD(id, $ids) DESC");
        }

        return view('rujukan-add', [
            'users' => $users->paginate(3),
        ]);
    }
}
