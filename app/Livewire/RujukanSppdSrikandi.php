<?php

namespace App\Livewire;

use App\Models\RujukanModel;
use Carbon\Carbon;
use Livewire\Component;

class RujukanSppdSrikandi extends Component
{
    public $id;
    public $alamat_rujukan = '';
    public $berangkat = '';
    public $pulang = '';
    public $nama = '';
    public $data;
    public $foto;
    public $edit = false;
    public $hari_berangkat = '';
    public $tgl_berangkat, $tgl_pulang;
    public function mount($id)
    {
        $data = RujukanModel::with('users', 'pns')->findOrFail($id);
        $this->data = $data;
        $this->id = $id;
        $this->alamat_rujukan = $data->alamat_rujukan;

        $this->berangkat = Carbon::parse($data->tanggal_berangkat)
            ->locale('id')
            ->isoFormat('dddd, D MMMM Y');
        $this->tgl_berangkat = Carbon::parse($data->pulang)
            ->locale('id')
            ->isoFormat(' D MMMM Y');
        $this->tgl_pulang = Carbon::parse($data->pulang)
            ->locale('id')
            ->isoFormat('D MMMM Y');
        $this->hari_berangkat = Carbon::parse($data->berangkat)
            ->locale('id')
            ->isoFormat('dddd');

        $this->nama = $data->nama_rujukan;
    }

    public function render()
    {
        return view('rujukan-sppd-srikandi');
    }
}
