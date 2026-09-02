<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RujukanModel;

class RujukanIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filter = '';
    public $from_date = '';
    public $to_date = '';

    public $temp_search = '';
    public $temp_filter = '';
    public $temp_from_date = '';
    public $temp_to_date = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => ''],
        'from_date' => ['except' => ''],
        'to_date' => ['except' => ''],
    ];

    public function applyFilter()
    {
        $this->search = $this->temp_search;
        $this->filter = $this->temp_filter;
        $this->from_date = $this->temp_from_date;
        $this->to_date = $this->temp_to_date;

        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->search = '';
        $this->filter = '';
        $this->from_date = '';
        $this->to_date = '';

        $this->temp_search = '';
        $this->temp_filter = '';
        $this->temp_from_date = '';
        $this->temp_to_date = '';

        $this->resetPage();
    }

    public function hapus($id)
    {
        $rujukan = RujukanModel::findOrFail($id);

        // 🔐 hanya boleh hapus rujukan HARI INI
        // if ($rujukan->created_at->toDateString() !== now()->toDateString()) {
        //     session()->flash('error', 'Rujukan hanya bisa dihapus di hari yang sama.');
        //     return;
        // }

        $rujukan->users()->detach();
        $rujukan->delete();

        session()->flash('message', 'Rujukan berhasil dihapus');
    }

    public function konfirmasi($id)
    {
        $rujukan = RujukanModel::findOrFail($id);

        $rujukan->status = 'confirmed';
        $rujukan->save();
        session()->flash('message', 'Rujukan berhasil dikonfirmasi');
    }

    public function render()
    {
        if (auth()->user()->jabatan != 'manajemen') {
            $pribadi = true;
        } else {
            $pribadi = false;
        }
        $rujukans = RujukanModel::with('users', 'pns')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->whereHas('users', function ($u) {
                        $u->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%")
                            ->orWhere('jabatan', 'like', "%{$this->search}%");
                    })
                        ->orWhereHas('pns', function ($p) {
                            $p->where('nama', 'like', "%{$this->search}%")
                                ->orWhere('nip', 'like', "%{$this->search}%")
                                ->orWhere('jabatan', 'like', "%{$this->search}%");
                        })
                        ->orWhere('nama_rujukan', 'like', "%{$this->search}%")
                        ->orWhere('alamat_rujukan', 'like', "%{$this->search}%")
                        ->orWhere('tempat', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filter, function ($q) {
                $q->whereHas('users', function ($u) {
                    $u->where('jabatan', $this->filter);
                });
            })
            ->when($this->from_date && $this->to_date, function ($q) {
                // Filter antara rentang tanggal
                $q->whereBetween('created_at', [
                    $this->from_date . ' 00:00:00',
                    $this->to_date . ' 23:59:59'
                ]);
            })
            ->when($this->from_date && !$this->to_date, function ($q) {
                // Filter dari tanggal tertentu ke atas
                $q->whereDate('created_at', '>=', $this->from_date);
            })
            ->when(!$this->from_date && $this->to_date, function ($q) {
                // Filter sampai tanggal tertentu
                $q->whereDate('created_at', '<=', $this->to_date);
            })
            ->when($pribadi, function ($q) {
                $q->whereHas('users', function ($u) {
                    $u->where('rujukan_user.user_id', auth()->user()->id);
                });
            })

            ->latest()
            ->paginate(30);



        return view('rujukan-index', [
            'rujukans' => $rujukans,
        ]);
    }
}
