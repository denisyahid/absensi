<div class="max-w-4xl mx-auto">
    <h2 class="mb-6 text-2xl font-bold">Buat Rujukan Baru</h2>

    <form wire:submit.prevent="simpan" class="space-y-6">
        <!-- Informasi Rujukan -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label>Perihal *</label>
                <input type="text" wire:model="perihal" class="w-full border rounded" required>
            </div>
            <div>
                <label>Waktu</label>
                <input type="text" wire:model="waktu" class="w-full border rounded">
            </div>
            <div>
                <label>Tempat</label>
                <input type="text" wire:model="tempat" class="w-full border rounded">
            </div>
            <div>
                <label>Alat Angkut</label>
                <select wire:model="alat_angkut" class="w-full border rounded">
                    <option value="roda empat">Roda Empat</option>
                    <option value="roda dua">Roda Dua</option>
                    <option value="pesawat">Pesawat</option>
                </select>
            </div>
            <div>
                <label>Tanggal Berangkat *</label>
                <input type="date" wire:model="tanggal_berangkat" class="w-full border rounded" required>
            </div>
            <div>
                <label>Tanggal Kembali *</label>
                <input type="date" wire:model="tanggal_kembali" class="w-full border rounded" required>
            </div>
            <div>
                <label>Biaya Perdin (Rp)</label>
                <input type="number" wire:model="biaya_perdin" class="w-full border rounded">
            </div>
        </div>

        <!-- Alamat dan Keterangan -->
        <div>
            <label>Alamat Rujukan *</label>
            <input type="text" wire:model="alamat_rujukan" class="w-full border rounded" required>
        </div>

        <div>
            <label>Nama Pasien / Keterangan *</label>
            <textarea wire:model="nama_rujukan" class="w-full border rounded" rows="3" required></textarea>
        </div>

        <!-- Upload Bukti -->
        <div>
            <label>Bukti Rujukan (Foto)</label>
            <input type="file" wire:model="bukti_rujukan" class="w-full">
            @if ($bukti_rujukan)
                <img src="{{ $bukti_rujukan->temporaryUrl() }}" class="h-32 mt-2">
            @endif
        </div>

        <!-- Daftar Peserta -->
        <div class="p-4 border rounded">
            <h3 class="mb-4 font-bold">Peserta Rujukan</h3>

            @foreach ($peserta as $index => $p)
                <div class="flex gap-4 p-3 mb-3 border rounded">
                    <!-- Pilih User -->
                    <div class="flex-1">
                        <label>User</label>
                        <select wire:model="peserta.{{ $index }}.user_id" class="w-full border rounded">
                            <option value="">Pilih User</option>
                            @foreach ($userOptions as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <input type="text" wire:model.live="searchUser" placeholder="Cari user..."
                            class="w-full mt-1 border rounded">
                    </div>

                    <!-- Pilih PNS (pinjam nama) -->
                    <div class="flex-1">
                        <label>Pinjam Nama PNS (opsional)</label>
                        <select wire:model="peserta.{{ $index }}.pns_id" class="w-full border rounded">
                            <option value="">-- Tidak Pinjam PNS --</option>
                            @foreach ($pnsOptions as $pns)
                                <option value="{{ $pns->id }}">
                                    {{ $pns->nama }} - NIP: {{ $pns->nip }}
                                </option>
                            @endforeach
                        </select>
                        <input type="text" wire:model.live="searchPns" placeholder="Cari PNS..."
                            class="w-full mt-1 border rounded">
                    </div>

                    <!-- Hapus -->
                    <div class="pt-6">
                        <button type="button" wire:click="removePeserta({{ $index }})"
                            class="px-3 py-1 text-white bg-red-500 rounded">
                            Hapus
                        </button>
                    </div>
                </div>
            @endforeach

            <button type="button" wire:click="addPeserta" class="px-4 py-2 text-white bg-blue-500 rounded">
                + Tambah Peserta
            </button>
        </div>

        <!-- Submit -->
        <div class="flex gap-4">
            <button type="button" onclick="history.back()" class="px-6 py-2 border rounded">
                Batal
            </button>
            <button type="submit" class="px-6 py-2 text-white bg-green-500 rounded">
                Simpan Rujukan
            </button>
        </div>
    </form>
</div>
