<div>
    <x-slot name="header">
        <div class="flex justify-between w-full">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Daftar Perjalanan Dinas Karyawan
            </h2>
            <div>
 <a target="_blank" href="/bukti-pelayanan-ambulance" class="px-3 py-2 text-xs text-white bg-blue-500 rounded-md ms-2">
                    Bukti Pelayanan Ambulance
                </a>
                <a href="/sppd/buat" class="px-3 py-2 text-xs text-white rounded-md bg-sky-500 ms-2">
                    Buat Perjalanan Dinas
                </a>
            </div>
        </div>
    </x-slot>

    <div>
        @if (auth()->user()->jabatan == 'manajemen')
            <script>
                function hapus(id, name) {
                    Swal.fire({
                        title: "Anda yakin?",
                        html: `<b>${name}</b> akan dihapus!`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Batal",
                        showLoaderOnConfirm: true,
                        allowOutsideClick: () => !Swal.isLoading(),
                        preConfirm: () => {
                            return new Promise((resolve) => {
                                @this.call('hapus', id);
                                resolve();
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: "Terhapus!",
                                html: `<b>${name}</b> berhasil dihapus.`,
                                icon: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            </script>
            <script>
                function terima(id, name) {
                    Swal.fire({
                        title: "Anda yakin?",
                        html: `<b>${name}</b> akan dikonfirmasi!`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Ya, Konfirmasi!",
                        cancelButtonText: "Batal",
                        showLoaderOnConfirm: true,
                        allowOutsideClick: () => !Swal.isLoading(),
                        preConfirm: () => {
                            return new Promise((resolve) => {
                                @this.call('konfirmasi', id);
                                resolve();
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: "Terkonfirmasi!",
                                html: `<b>${name}</b> berhasil dikonfirmasi.`,
                                icon: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            </script>
        @endif
        <section class="container mx-auto">
            <div class="px-10 mt-6">
                <!-- Loading Indicator -->
                <div wire:loading.delay class="p-4 mb-4 text-blue-700 bg-blue-100 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Memuat data...
                    </div>
                </div>

                <!-- Flash Message -->
                @if (session()->has('message'))
                    <div class="p-4 mb-4 text-green-700 bg-green-100 rounded-lg">
                        {{ session('message') }}
                    </div>
                @endif

                <!-- Filter Form -->
                <div class="w-full p-2 mb-6">
                    <form wire:submit.prevent="applyFilter" class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <!-- Input Search -->
                        <div>
                            <input type="text" wire:model="temp_search"
                                class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Cari nama, keterangan, tempat...">
                            @error('temp_search')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Select Filter Jabatan -->
                        <div>
                            <select wire:model="temp_filter"
                                class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Jabatan</option>
                                <option value="ranap">Ranap</option>
                                <option value="rawat_jalan">Rawat jalan</option>
                                <option value="farmasi">Farmasi</option>
                                <option value="administrasi">Administrasi</option>
                                <option value="igd">Igd</option>
                                <option value="lab">Lab</option>
                                <option value="radiologi">Radiologi</option>
                                <option value="cssd">Cssd</option>
                                <option value="ambulance">Supir Ambulance</option>
                                <option value="laundry">Laundry</option>
                                <option value="satpam">Satpam</option>
                                <option value="cs">Cleaning Service</option>
                                <option value="manajemen">Manajemen</option>
                            </select>
                            @error('temp_filter')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Filter Dari Tanggal -->
                        <div>
                            <input type="date" wire:model="temp_from_date"
                                class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                max="{{ $temp_to_date ?? date('Y-m-d') }}">
                            @error('temp_from_date')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Filter Sampai Tanggal -->
                        <div>
                            <input type="date" wire:model="temp_to_date"
                                class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                min="{{ $temp_from_date ?? '' }}">
                            @error('temp_to_date')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Tombol Action -->
                        <div class="flex gap-2">
                            <button type="submit"
                                class="px-6 py-2 text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Terapkan Filter
                            </button>

                            <button type="button" wire:click="resetFilter"
                                class="px-6 py-2 text-gray-700 bg-gray-200 rounded-xl hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                                Reset
                            </button>
                        </div>
                    </form>

                    <!-- Info Filter Aktif -->
                    @if ($search || $filter || $from_date || $to_date)
                        <div class="p-4 mt-4 rounded-lg bg-blue-50">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium">Filter aktif:</span>
                                    @if ($search)
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-sm bg-blue-100 rounded-full">
                                            Pencarian: "{{ $search }}"
                                            <button wire:click="$set('temp_search', '')"
                                                wire:click.prevent="applyFilter"
                                                class="ml-2 text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($filter)
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-sm bg-blue-100 rounded-full">
                                            Jabatan: {{ ucfirst(str_replace('_', ' ', $filter)) }}
                                            <button wire:click="$set('temp_filter', '')"
                                                wire:click.prevent="applyFilter"
                                                class="ml-2 text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($from_date || $to_date)
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-sm bg-blue-100 rounded-full">
                                            Tanggal:
                                            @if ($from_date && $to_date)
                                                {{ \Carbon\Carbon::parse($from_date)->translatedFormat('d F Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($to_date)->translatedFormat('d F Y') }}
                                            @elseif($from_date)
                                                Dari
                                                {{ \Carbon\Carbon::parse($from_date)->translatedFormat('d F Y') }}
                                            @elseif($to_date)
                                                Sampai
                                                {{ \Carbon\Carbon::parse($to_date)->translatedFormat('d F Y') }}
                                            @endif
                                            <button wire:click="$set('temp_from_date', '')"
                                                wire:click="$set('temp_to_date', '')" wire:click.prevent="applyFilter"
                                                class="ml-2 text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </span>
                                    @endif
                                </div>
                                <button wire:click="resetFilter" class="text-sm text-red-600 hover:text-red-800">
                                    Hapus semua filter
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Data Table -->
                <div class="overflow-hidden border rounded-lg dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3.5 text-sm font-normal text-left text-gray-500 dark:text-gray-400">
                                    Nama
                                </th>
                                <th class="px-4 py-3.5 text-sm font-normal text-left text-gray-500 dark:text-gray-400">
                                    Keterangan
                                </th>
                                <th class="px-4 py-3.5 text-sm font-normal text-left text-gray-500 dark:text-gray-400">
                                    Tanggal
                                </th>
                                <th class="relative py-3.5 px-4">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            @forelse ($rujukans as $rujukan)
                                <tr
                                    class="border-b-4 @if ($rujukan->status == 'confirmed') bg-green-50 @elseif($rujukan->status == null) bg-red-50 @endif">
                                    <td class="flex px-4 py-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <div>
                                            @foreach ($rujukan->users as $user)
                                                <div
                                                    class="flex items-center gap-3 px-4 mb-2 bg-white border shadow-xl rounded-2xl">
                                                    <div>
                                                        <h2 class="text-xs font-semibold">{{ $user->name }}</h2>
                                                        <p class="text-xs text-yellow-600">{{ $user->jabatan }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div>
                                            @foreach ($rujukan->pns as $pns)
                                                <div
                                                    class="flex items-center gap-3 px-3 mb-2 bg-white border shadow-xl rounded-2xl">
                                                    <div>
                                                        <h2 class="text-xs font-semibold">{{ $pns->nama }}</h2>
                                                        <p class="text-xs text-yellow-600">{{ $pns->jabatan }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300">
                                        <div class="flex items-center p-3 bg-white shadow-sm rounded-xl">
                                            @if ($rujukan->bukti_rujukan)
                                                <a href="/storage/{{ $rujukan->bukti_rujukan }}" target="_blank">
                                                    <img class="object-cover w-10 h-10 rounded me-3"
                                                        src="/storage/{{ $rujukan->bukti_rujukan }}"
                                                        alt="Bukti Rujukan" onerror="this.style.display='none'">
                                                </a>
                                            @else
                                                <div class="me-3">
                                                    <span
                                                        class="px-2 py-1 text-xs text-center text-white bg-red-500 rounded-2xl">
                                                        Bukti Rujukan
                                                    </span>
                                                </div>
                                            @endif
                                            @if ($rujukan->kuitansi_bensin)
                                                <a href="/storage/{{ $rujukan->kuitansi_bensin }}" target="_blank">
                                                    <img class="object-cover w-10 h-10 rounded me-3"
                                                        src="/storage/{{ $rujukan->kuitansi_bensin }}"
                                                        alt="Kuitansi Bensin" onerror="this.style.display='none'">
                                                </a>
                                            @else
                                                <div class="me-3">
                                                    <span
                                                        class="px-2 py-1 text-xs text-center text-white bg-red-500 rounded-2xl">
                                                        Kuitansi Bensin
                                                    </span>
                                                </div>
                                            @endif
                                            <div>
                                                <b>{{ $rujukan->tempat }}</b>
                                                <p class="mt-1">{{ $rujukan->nama_rujukan }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300">
                                        {{-- <div class="mb-1 text-xs text-center bg-white shadow-sm rounded-xl">
                                                {{ $rujukan->nomor_surat }}
                                            </div> --}}
                                        <div class="text-center bg-white shadow-sm rounded-xl">
                                            {{ $rujukan->tanggal_berangkat->translatedFormat('d F Y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm whitespace-nowrap">
                                        <div class="flex flex-col gap-2">
                                            <a target="_blank" href="/rujukan/sppd/{{ $rujukan->id }}"
                                                class="px-3 py-2 text-xs text-center text-white bg-green-500 rounded-xl hover:bg-green-600">
                                                Print SPPD
                                            </a>
                                             <a target="_blank" href="/rujukan/sppd-srikandi/{{ $rujukan->id }}"
                                                class="px-3 py-2 text-xs text-center text-white bg-yellow-500 rounded-xl hover:bg-yellow-600">
                                                Print SPPD Srikandi
                                            </a>
                                            @if ($rujukan->status == null)
                                                <div class="grid grid-cols-2 gap-2">

                                                    <a href="/rujukan/{{ $rujukan->id }}/edit"
                                                        class="p-2 text-blue-500 transition-colors duration-200 bg-blue-100 border-2 rounded hover:bg-blue-200 dark:hover:bg-gray-800 hover:text-blue-600 dark:text-gray-300">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor" class="w-5 h-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                        </svg>
                                                    </a>
                                                    <button type="button"
                                                        onclick="hapus({{ $rujukan->id }}, '{{ addslashes($rujukan->nama_rujukan . ' - ' . $rujukan->alamat_rujukan) }}')"
                                                        class="p-2 text-red-500 transition-colors duration-200 bg-red-200 border-2 rounded dark:hover:bg-red-800 hover:text-red-500 hover:bg-red-300">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="1.5"
                                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </div>
						 @if (auth()->user()->jabatan == 'manajemen')
                                                    <button type="button"
                                                        onclick="terima({{ $rujukan->id }}, '{{ addslashes($rujukan->nama_rujukan . ' - ' . $rujukan->alamat_rujukan) }}')"
                                                        class="px-2 py-1 text-xs text-white bg-blue-500 rounded hover:bg-blue-600">
                                                        Konfirmasi
                                                    </button>
                                                @endif                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-16 h-16 mb-4 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-lg font-medium">Tidak ada data ditemukan</p>
                                            <p class="mt-1">
                                                @if ($search || $filter || $from_date || $to_date)
                                                    Coba gunakan filter yang berbeda
                                                @else
                                                    Belum ada data rujukan
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-5">
                    {{ $rujukans->links() }}
                </div>
            </div>
        </section>
    </div>
</div>
