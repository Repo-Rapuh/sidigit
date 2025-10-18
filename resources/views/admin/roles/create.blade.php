@extends('layouts.app')

@section('title', 'Tambah Role')

@section('content')
    <div class="space-y-6">
        <div class="flex items-baseline justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Tambah Role Baru</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Tentukan nama peran, slug, dan hak akses yang diberikan.
                </p>
            </div>
            <a href="{{ route('roles.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                Kembali
            </a>
        </div>

        <div class="space-y-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-950/70">
            <form action="{{ route('roles.store') }}" method="POST" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Role
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            required>
                        @error('name')
                            <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Slug
                        </label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                            class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            required>
                        @error('slug')
                            <p class="mt-1 text-sm text-error-500 dark:text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hak Akses Izin</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pilih izin aksi yang diberikan untuk peran ini.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($menus as $menu)
                            <div class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950/50">
                                <div class="border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-800 dark:border-gray-900 dark:text-white">
                                    {{ $menu->name }}
                                </div>
                                <div class="flex-1 space-y-4 px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    @forelse ($menu->children->sortBy('order') as $child)
                                        <div class="space-y-2">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $child->name }}</p>
                                            <div class="space-y-2 rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                                @foreach ($child->permissions as $permission)
                                                    <label class="flex items-center gap-3 text-sm">
                                                        <input type="checkbox" name="permissions[]"
                                                            value="{{ $permission->id }}" id="perm-{{ $permission->id }}"
                                                            class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900"
                                                            @checked(is_array(old('permissions')) && in_array($permission->id, old('permissions')))>
                                                        <span>{{ $permission->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @empty
                                        <div class="space-y-2">
                                            @foreach ($menu->permissions as $permission)
                                                <label class="flex items-center gap-3 text-sm">
                                                    <input type="checkbox" name="permissions[]"
                                                        value="{{ $permission->id }}" id="perm-{{ $permission->id }}"
                                                        class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900"
                                                        @checked(is_array(old('permissions')) && in_array($permission->id, old('permissions')))>
                                                    <span>{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hak Akses Menu</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tentukan menu yang dapat dilihat oleh peran ini pada sidebar.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($menus as $menu)
                            <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800 dark:bg-gray-950/50">
                                <label class="flex items-start gap-3 text-sm font-semibold text-gray-900 dark:text-white">
                                    <input type="checkbox" name="menus[]" value="{{ $menu->id }}" id="menu-{{ $menu->id }}"
                                        class="mt-0.5 size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900"
                                        @checked(is_array(old('menus')) && in_array($menu->id, old('menus')))>
                                    {{ $menu->name }}
                                </label>
                                @if ($menu->children->isNotEmpty())
                                    <div class="mt-3 space-y-2 border-l border-dashed border-gray-200 pl-4 dark:border-gray-700">
                                        @foreach ($menu->children->sortBy('order') as $child)
                                            <label class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                                                <input type="checkbox" name="menus[]" value="{{ $child->id }}"
                                                    id="menu-{{ $child->id }}"
                                                    class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/40 dark:border-gray-700 dark:bg-gray-900"
                                                    @checked(is_array(old('menus')) && in_array($child->id, old('menus')))>
                                                {{ $child->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:ring-offset-gray-950">
                        Simpan Role
                    </button>
                    <a href="{{ route('roles.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:text-brand-400">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
