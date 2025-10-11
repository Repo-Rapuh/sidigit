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
                    {{-- Form Input untuk Nama Role dan Slug --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama Role</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                    id="slug" name="slug" value="{{ old('slug') }}" required>
                                <small class="form-text text-muted">Gunakan huruf kecil dan tanda hubung (-).</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5>Hak Akses Izin (Permissions)</h5>
                    <p class="text-muted">Pilih izin yang akan diberikan untuk peran ini.</p>

                    {{-- Desain Baru Menggunakan Row --}}
                    <div class="row mt-3">
                        @forelse ($menus as $menu)
                            <div class="col-12 mb-4">
                                {{-- HEADING PARENT MENU --}}
                                <h6 class="mb-2 text-primary fw-bold">{{ $menu->name }}</h6>

                                {{-- Cek jika parent menu punya permission langsung --}}
                                @if ($menu->permissions->isNotEmpty())
                                    <div class="d-flex flex-wrap">
                                        @foreach ($menu->permissions as $permission)
                                            <div class="form-check form-check-inline me-3">
                                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                                    value="{{ $permission->id }}" id="perm-{{ $permission->id }}">
                                                <label class="form-check-label"
                                                    for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Cek jika parent menu punya sub-menu --}}
                                @if ($menu->children->isNotEmpty())
                                    @foreach ($menu->children->sortBy('order') as $child)
                                        <div class="ms-3 mt-3">
                                            {{-- HEADING SUB-MENU --}}
                                            <strong class="d-block mb-2">{{ $child->name }}</strong>

                                            {{-- LIST FORM CHECKBOX --}}
                                            <div class="d-flex flex-wrap">
                                                @forelse ($child->permissions as $permission)
                                                    <div class="form-check form-check-inline me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                                            value="{{ $permission->id }}" id="perm-{{ $permission->id }}">
                                                        <label class="form-check-label"
                                                            for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                    </div>
                                                @empty
                                                    <small class="text-muted fst-italic">Tidak ada permission</small>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <hr class="mt-3">
                            </div>
                        @empty
                            <p class="text-center">Tidak ada menu atau permission yang tersedia.</p>
                        @endforelse
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
