<x-slot name="header">
    <div class="flex items-center justify-between px-4 py-3 shadow">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            Isi Form Rujukan
        </h2>

        <a href="/rujukan" wire:navigate
            class="px-3 py-2 text-xs text-white transition rounded bg-slate-500 hover:bg-slate-600">
            Kembali
        </a>
    </div>
</x-slot>

<div class="flex justify-center px-4 py-6">
    <form wire:submit.prevent="simpan"
        class="w-full max-w-2xl p-6 space-y-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        {{-- PILIH USER --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Tambahkan Teman (User)
            </label>
            <div>
                <input type="text" wire:model.live="search" placeholder="Cari user..."
                    class="w-full px-4 py-2 mb-4 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">
            </div>
            {{-- USER TERPILIH --}}
            @if (!empty($selectedUsers))
                <div class="p-3 mb-4 border rounded-lg bg-blue-50 dark:bg-gray-900">
                    <p class="mb-2 text-xs font-semibold text-blue-700 dark:text-blue-400">
                        User Terpilih
                    </p>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($users->whereIn('id', $selectedUsers) as $user)
                            <span
                                class="flex items-center gap-2 px-3 py-1 text-sm bg-white border rounded-full shadow-sm dark:bg-gray-800">

                                {{ $user->name }}

                            </span>
                        @endforeach
                    </div>
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
                            <span class="ml-2 text-xs font-semibold text-blue-600">
                                (Dipilih)
                            </span>
                        @endif
                    </label>
                @endforeach
            </div>
            <div>
                {{ $users->links() }}
            </div>

            @error('selectedUsers')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>



        {{-- ALAMAT RUJUKAN --}}
        <div>
            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                Tempat Tujuan Rujukan
            </label>

            <input type="text" wire:model.defer="alamat_rujukan"
                class="w-full px-4 py-3 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-900 dark:border-gray-600">

            @error('alamat_rujukan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- UPLOAD FOTO --}}
        <div>
            <label
                class="flex flex-col items-center justify-center gap-2 p-6 text-center border-2 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
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

        {{-- KETERANGAN --}}
        <div>
            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                Keterangan / Nama Pasien
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
            Simpan Rujukan
        </button>

    </form>
</div>
