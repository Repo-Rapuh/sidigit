@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <h3>Tambah Permission Baru</h3>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('permissions.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Nama Permission</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug"
                            name="slug" value="{{ old('slug') }}" required>
                        <small class="form-text text-muted">Gunakan format: `resource.aksi` (contoh: `user.view`).</small>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="menu_id">Induk Menu</label>
                        <select class="form-select @error('menu_id') is-invalid @enderror" id="menu_id" name="menu_id">
                            <option value="">-- Tidak Terikat Menu --</option>
                            @foreach ($menus as $menu)
                                <option value="{{ $menu->id }}" {{ old('menu_id') == $menu->id ? 'selected' : '' }}>
                                    {{ $menu->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Pilih menu untuk mengelompokkan izin ini.</small>
                        @error('menu_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('permissions.index') }}" class="btn btn-light">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
