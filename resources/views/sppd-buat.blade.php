<div class="flex justify-center px-4 py-6">
    <form wire:submit.prevent="simpan"
        class="w-full max-w-4xl p-6 space-y-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        {{-- NOMOR SURAT (READ-ONLY) --}}
        <div class="hidden">
            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                Nomor Surat *
            </label>
            <input type="text" value="{{ $nomor_surat }}" readonly
                class="w-full px-4 py-3 border rounded-lg bg-gray-50 dark:bg-gray-900 dark:border-gray-600">
            <p class="mt-1 text-xs text-gray-500">Nomor surat akan di-generate otomatis</p>
        </div>

        {{-- PILIH USER --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Tambahkan Teman (User)
            </label>
            <div>
                <input type="text" wire:model.live="search" placeholder="Cari user..."
                    class="w-full px-4 py-2 mb-4 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">
            </div>
            @if (!empty($peserta))
                <div class="p-4 mt-6 border rounded">
                    <p class="mb-3 text-sm font-semibold">Peserta Terpilih</p>

                    @foreach ($peserta as $index => $p)
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            {{-- USER --}}
                            <input type="text" class="w-full bg-gray-100 border rounded"
                                value="{{ $p['user_name'] }}" readonly>

                            {{-- PNS --}}
                            <div>
                                <input type="text"
                                    wire:model.live.debounce.500ms="peserta.{{ $index }}.searchPns"
                                    placeholder="Cari PNS..." class="w-full mb-1 border rounded">

                                <select wire:model.live="peserta.{{ $index }}.pns_id"
                                    class="w-full border rounded">
                                    <option value="">-- Pilih ASN --</option>
                                    @foreach ($pnsOptions[$index] ?? [] as $pns)
                                        <option value="{{ $pns['id'] ?? '' }}">
                                            {{ $pns['nama'] }} - {{ $pns['nip'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif


            <div class="grid grid-cols-3 gap-2">
                @foreach ($users as $user)
                    <label
                        class="flex items-center gap-2 p-2 border rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900">
                        <input type="checkbox" wire:model.live="selectedUsers" value="{{ $user->id }}"
                            class="text-blue-600 rounded">
                        <div>
                            <p class="text-sm font-semibold">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->jabatan }}</p>
                        </div>
                        @if (in_array($user->id, $selectedUsers ?? []))
                            <span class="ml-2 text-xs font-semibold text-blue-600">(Dipilih)</span>
                        @endif
                    </label>
                @endforeach
            </div>
            {{-- <div>
                {{ $users->links() }}
            </div> --}}
            @error('selectedUsers')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- FORM DUA KOLOM --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- KOLOM KIRI --}}
            <div class="space-y-6">
                {{-- PERIHAL --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Perihal *
                    </label>
                    <input type="text" wire:model.defer="perihal" placeholder="Masukkan perihal rujukan"
                        class="w-full px-4 py-3 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">
                    @error('perihal')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- WAKTU --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Waktu *
                    </label>
                    <input type="text" wire:model.defer="waktu"
                        class="w-full px-4 py-3 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">
                    @error('waktu')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- TEMPAT --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tempat *
                    </label>
                    <input type="text" wire:model.defer="tempat" placeholder="Masukkan tempat rujukan"
                        class="w-full px-4 py-3 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">
                    @error('tempat')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ALAT ANGKUT --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Alat Angkut *
                    </label>
                    <select wire:model.defer="alat_angkut"
                        class="w-full px-4 py-3 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">
                        <option value="roda empat">Roda Empat</option>
                        <option value="roda dua">Roda Dua</option>
                        <option value="pesawat">Pesawat</option>
                        <option value="kereta">Kereta</option>
                        <option value="kapal">Kapal</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                    @error('alat_angkut')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- KOLOM KANAN --}}
            <div class="space-y-6">
                {{-- BIAYA PERDIN --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Biaya Perdin (Rp) *
                    </label>
                    <input type="number" wire:model.defer="biaya_perdin" min="0" step="1000"
                        class="w-full px-4 py-3 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">
                    @error('biaya_perdin')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- TANGGAL BERANGKAT --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Berangkat *
                    </label>
                    <input type="date" wire:model.defer="tanggal_berangkat"
                        class="w-full px-4 py-3 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">
                    @error('tanggal_berangkat')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- TANGGAL KEMBALI --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Kembali *
                    </label>
                    <input type="date" wire:model.defer="tanggal_kembali"
                        class="w-full px-4 py-3 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">
                    @error('tanggal_kembali')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ALAMAT RUJUKAN --}}
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Alamat Rujukan *
                    </label>
                    <input type="text" wire:model.defer="alamat_rujukan" placeholder="Masukkan alamat rujukan"
                        class="w-full px-4 py-3 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">
                    @error('alamat_rujukan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- UPLOAD FOTO --}}
        <div class="grid hidden grid-cols-2">

            <div class="me-3">
                <label
                    class="flex flex-col items-center justify-center gap-2 p-6 text-center border-2 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span class="text-sm text-gray-500">
                        Upload bukti foto saat berangkat
                    </span>
                    <input type="file" wire:model="bukti_rujukan" class="hidden">
                </label>
                @error('bukti_rujukan')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                {{-- PREVIEW --}}
                @if ($bukti_rujukan)
                    <div class="mt-4">
                        <img src="{{ str($bukti_rujukan->temporaryUrl())->replace('/livewire/preview-file/', '/storage/tmp/') }}"
                            class="object-cover w-48 h-48 border rounded-lg">
                    </div>
                @endif
            </div>
            <div>
                <label
                    class="flex flex-col items-center justify-center gap-2 p-6 text-center border-2 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span class="text-sm text-gray-500">
                        Upload bukti kuitansi Bensin
                    </span>
                    <input type="file" wire:model="kuitansi_bensin" class="hidden">
                </label>
                @error('kuitansi_bensin')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                {{-- PREVIEW --}}
                @if ($kuitansi_bensin)
                    <div class="mt-4">
                        <img src="{{ str($kuitansi_bensin->temporaryUrl())->replace('/livewire/preview-file/', '/storage/tmp/') }}"
                            class="object-cover w-48 h-48 border rounded-lg">
                    </div>
                @endif
            </div>
        </div>
        {{-- KETERANGAN / NAMA PASIEN --}}
        <div>
            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                Keterangan / Nama Pasien *
            </label>
            <textarea wire:model.defer="nama_rujukan"
                class="w-full px-4 py-3 border rounded-lg min-h-[120px] focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600"
                placeholder="Isi keterangan rujukan..."></textarea>
            @error('nama_rujukan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- LOADING --}}
        <div wire:loading class="text-sm text-blue-500">
            Menyimpan data...
        </div>

        {{-- SUBMIT --}}
        <button type="submit"
            class="w-full py-3 font-semibold text-white transition bg-blue-600 rounded-lg hover:bg-blue-500 disabled:opacity-50"
            wire:loading.attr="disabled">
            Simpan SPPD
        </button>
    </form>
</div>
