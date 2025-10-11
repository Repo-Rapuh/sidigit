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
                                <small class="form-text text-muted">Gunakan huruf kecil dan tanda hubung (-).</small>
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5>Hak Akses Izin (Permissions)</h5>
                    <p class="text-muted">Pilih izin yang akan diberikan untuk peran ini.</p>

                    <div class="row mt-3">
                        @forelse ($menus as $menu)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">{{ $menu->name }}</h6>
                                    </div>
                                    <div class="card-body">
                                        {{-- Jika menu adalah parent --}}
                                        @if($menu->children->isNotEmpty())
                                            @foreach ($menu->children->sortBy('order') as $child)
                                                <div class="mb-3">
                                                    <strong>{{ $child->name }}</strong>
                                                    <div class="ms-3 mt-2">
                                                        @forelse ($child->permissions as $permission)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm-{{ $permission->id }}">
                                                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                                    {{ $permission->name }}
                                                                </label>
                                                            </div>
                                                        @empty
                                                            <small class="text-muted fst-italic">Tidak ada permission</small>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            @endforeach
                                        {{-- Jika menu tidak punya anak --}}
                                        @else
                                            @forelse ($menu->permissions as $permission)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm-{{ $permission->id }}">
                                                    <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                            @empty
                                                <small class="text-muted fst-italic">Tidak ada permission</small>
                                            @endforelse
                                        @endif
                                    </div>
                                </div>
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
