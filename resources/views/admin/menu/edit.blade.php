@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href=" {{ asset('assets/extensions/choices.js/public/assets/styles/choices.css') }}">
@endpush

@section('content')
    <div class="page-heading">
        {{-- Perubahan 1: Judul Halaman --}}
        <h3>Edit Menu: {{ $menu->name }}</h3>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-body">
                {{-- Perubahan 2: Form Action --}}
                <form action="{{ route('menus.update', $menu) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Perubahan 3: Tambahkan Method PUT --}}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama Menu</label>
                                {{-- Perubahan 4: Isi Value dengan data yang ada --}}
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $menu->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="parent_id">Induk Menu (Parent)</label>
                                <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id"
                                    name="parent_id">
                                    <option value="">-- Tidak Ada Induk --</option>
                                    @foreach ($parentMenus as $parent)
                                        {{-- Perubahan 4: Pilih option sesuai data yang ada --}}
                                        <option value="{{ $parent->id }}"
                                            {{ old('parent_id', $menu->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="route_name">Route Name</label>
                                <input type="text" class="form-control @error('route_name') is-invalid @enderror"
                                    id="route_name" name="route_name" value="{{ old('route_name', $menu->route_name) }}">
                                <small class="form-text text-muted">Kosongkan jika ini adalah menu induk (parent).</small>
                                @error('route_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            {{-- edit.blade.php --}}
                            <div class="form-group">
                                <label for="icon-select">Ikon</label>
                                <select class="choices form-select @error('icon') is-invalid @enderror" id="icon-select"
                                    name="icon">
                                    <option value="">-- Pilih Ikon --</option>
                                    @foreach ($icons as $icon)
                                        {{-- Pastikan `selected` berfungsi dengan benar untuk halaman edit --}}
                                        <option value="{{ $icon }}"
                                            {{ old('icon', $menu->icon) == $icon ? 'selected' : '' }}>
                                            <i class="{{ $icon }}"></i> {{ $icon }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('icon')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="order">Urutan Tampil</label>
                                <input type="number" class="form-control @error('order') is-invalid @enderror"
                                    id="order" name="order" value="{{ old('order', $menu->order) }}" required>
                                @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('menus.index') }}" class="btn btn-light">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script src="{{ asset('assets/extensions/choices.js/public/assets/scripts/choices.js') }}"></script>
    <script>
        let choices = document.querySelectorAll(".choices")
        let initChoice
        for (let i = 0; i < choices.length; i++) {
            if (choices[i].classList.contains("multiple-remove")) {
                initChoice = new Choices(choices[i], {
                    delimiter: ",",
                    editItems: true,
                    maxItemCount: -1,
                    removeItemButton: true,
                })
            } else {
                initChoice = new Choices(choices[i])
            }
        }
    </script>
@endpush
