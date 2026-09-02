<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pns;
use Illuminate\Http\Request;

class PnsController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->get('search');

        $pns = Pns::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($pns) {
                return [
                    'id' => $pns->id,
                    'nama' => $pns->nama,
                    'nip' => $pns->nip,
                    'pangkat' => $pns->pangkat,
                    'golongan' => $pns->golongan,
                    'jabatan' => $pns->jabatan,
                ];
            });

        return response()->json($pns);
    }
}
