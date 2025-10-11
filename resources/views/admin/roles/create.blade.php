@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <h3>Tambah Role Baru</h3>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    {{-- Input Nama Role & Slug --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama Role</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" required>
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- BAGIAN HAK AKSES PERMISSIONS --}}
                    <h5>Hak Akses Izin (Permissions)</h5>
                    <p class="text-muted">Pilih izin aksi yang akan diberikan untuk peran ini.</p>
                    <div class="row mt-3">
                        @foreach ($menus as $menu)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-light"><h6 class="mb-0">{{ $menu->name }}</h6></div>
                                    <div class="card-body">
                                        @forelse ($menu->children->sortBy('order') as $child)
                                            <div class="mb-3">
                                                <strong>{{ $child->name }}</strong>
                                                <div class="ms-3 mt-2">
                                                    @foreach ($child->permissions as $permission)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm-{{ $permission->id }}" {{ is_array(old('permissions')) && in_array($permission->id, old('permissions')) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @empty
                                            @foreach ($menu->permissions as $permission)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm-{{ $permission->id }}" {{ is_array(old('permissions')) && in_array($permission->id, old('permissions')) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                </div>
                                            @endforeach
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    {{-- BAGIAN HAK AKSES MENU --}}
                    <h5>Hak Akses Menu</h5>
                    <p class="text-muted">Pilih menu yang dapat dilihat oleh peran ini di sidebar.</p>
                    <div class="row mt-3">
                        @foreach ($menus as $menu)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="menus[]" value="{{ $menu->id }}" id="menu-{{ $menu->id }}" {{ is_array(old('menus')) && in_array($menu->id, old('menus')) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="menu-{{ $menu->id }}">{{ $menu->name }}</label>
                                </div>
                                @if($menu->children->isNotEmpty())
                                    <div class="ms-4 mt-2">
                                        @foreach ($menu->children->sortBy('order') as $child)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="menus[]" value="{{ $child->id }}" id="menu-{{ $child->id }}" {{ is_array(old('menus')) && in_array($child->id, old('menus')) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="menu-{{ $child->id }}">{{ $child->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('roles.index') }}" class="btn btn-light">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
