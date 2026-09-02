<div class="space-y-4">
    <!-- Header dengan Search dan Info -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Pilih User</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span id="selected-count">{{ count($tempSelectedUsers) }}</span> user dipilih
            </p>
        </div>

        @if (count($tempSelectedUsers) > 0)
            <div class="flex gap-2">
                <button type="button" wire:click="$toggle('showSelected')"
                    class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-300">
                    <span wire:loading.remove wire:target="$toggle('showSelected')">
                        @if ($showSelected)
                            Sembunyikan
                        @else
                            Tampilkan
                        @endif
                    </span>
                    <span wire:loading wire:target="$toggle('showSelected')">...</span> Terpilih
                </button>
                <button type="button" wire:click="clearAll" wire:confirm="Yakin hapus semua pilihan?"
                    class="px-3 py-1 text-sm text-red-600 bg-red-100 rounded hover:bg-red-200 dark:bg-red-900 dark:text-red-300">
                    Hapus Semua
                </button>
            </div>
        @endif
    </div>

    <!-- Search Bar -->
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input type="text" wire:model.live="search" placeholder="Cari nama, email, atau jabatan..."
            class="w-full py-2 pl-10 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
    </div>

    <!-- Selected Users (Tags/Badges) -->
    @if ($showSelected && count($tempSelectedUsers) > 0)
        <div class="p-4 rounded-lg bg-blue-50 dark:bg-gray-800">
            <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium text-blue-800 dark:text-blue-300">User Terpilih:</h4>
                <span class="text-sm text-blue-600">{{ count($tempSelectedUsers) }} user</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($selectedUsersData as $user)
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 text-sm bg-white border border-blue-200 rounded-full shadow-sm dark:bg-gray-700 dark:border-blue-600">
                        @if ($user->foto)
                            <img src="{{ asset('storage/' . $user->foto) }}" alt="{{ $user->name }}"
                                class="w-5 h-5 rounded-full"
                                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF'">
                        @endif
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $user->name }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">({{ $user->jabatan }})</span>
                        @if ($user->id != auth()->user()->id)
                            <button type="button" wire:click="removeUser({{ $user->id }})"
                                class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @else
                            <span class="text-xs text-blue-500">(Anda)</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Users List dengan Select All -->
    <div class="border rounded-lg shadow-sm dark:border-gray-700">
        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800">
            <div class="flex items-center space-x-3">
                <input type="checkbox" wire:model="selectAll" id="select-all"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="select-all" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Pilih Semua di Halaman Ini
                </label>
            </div>
            <div class="text-sm text-gray-500">
                Menampilkan {{ $users->count() }} dari {{ $totalUsers }} user
            </div>
        </div>

        <div class="overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700 max-h-96">
            @forelse($users as $user)
                <div
                    class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-800 {{ $user->id == auth()->user()->id ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                    <div class="flex items-center space-x-3">
                        @if ($user->id != auth()->user()->id)
                            <input type="checkbox" wire:model="tempSelectedUsers" value="{{ $user->id }}"
                                id="user-{{ $user->id }}"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        @else
                            <input type="checkbox" checked disabled
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        @endif

                        <div class="flex items-center space-x-3">
                            @if ($user->foto)
                                <img src="{{ asset('storage/' . $user->foto) }}" alt="{{ $user->name }}"
                                    class="w-10 h-10 rounded-full"
                                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF'">
                            @else
                                <div
                                    class="flex items-center justify-center w-10 h-10 text-white bg-blue-500 rounded-full">
                                    <span class="font-medium">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif

                            <div>
                                <label for="user-{{ $user->id }}"
                                    class="block text-sm font-medium text-gray-700 cursor-pointer dark:text-gray-300">
                                    {{ $user->name }}
                                    @if ($user->id == auth()->user()->id)
                                        <span class="text-blue-600">(Anda)</span>
                                    @endif
                                </label>
                                <div class="flex items-center gap-2 mt-1">
                                    <span
                                        class="px-2 py-0.5 text-xs font-medium text-gray-600 bg-gray-100 rounded dark:bg-gray-700 dark:text-gray-400">
                                        {{ $user->jabatan }}
                                    </span>
                                    <span class="text-xs text-gray-500 truncate max-w-[150px]">
                                        {{ $user->email }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($user->id != auth()->user()->id)
                        @if (in_array($user->id, $tempSelectedUsers))
                            <button type="button" wire:click="removeUser({{ $user->id }})"
                                class="px-3 py-1 text-xs text-red-600 bg-red-100 rounded hover:bg-red-200 dark:bg-red-900 dark:text-red-300">
                                Hapus
                            </button>
                        @else
                            <button type="button" wire:click="addUser({{ $user->id }})"
                                class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-300">
                                + Tambah
                            </button>
                        @endif
                    @else
                        <span
                            class="px-2 py-1 text-xs text-green-600 bg-green-100 rounded dark:bg-green-900 dark:text-green-300">
                            Anda
                        </span>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Tidak ada user ditemukan</p>
                    @if ($search)
                        <p class="text-xs text-gray-400">Coba gunakan kata kunci lain</p>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="p-3 border-t bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <script>
        // Update selected count dengan Livewire
        document.addEventListener('livewire:load', function() {
            // Update counter setiap kali komponen diupdate
            Livewire.hook('message.processed', (message, component) => {
                const selectedCount = component.get('tempSelectedUsers').length;
                const selectedCountEl = document.getElementById('selected-count');
                if (selectedCountEl) {
                    selectedCountEl.textContent = selectedCount;
                }

                // Update select all checkbox
                const selectAllCheckbox = document.getElementById('select-all');
                if (selectAllCheckbox) {
                    const selectedUsers = component.get('tempSelectedUsers');
                    // Reset indeterminate state
                    selectAllCheckbox.indeterminate = false;
                }
            });
        });
    </script>
</div>
