@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <h3>Edit Role: {{ $role->name }}</h3>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Input Nama Role & Slug --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama Role</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $role->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                    id="slug" name="slug" value="{{ old('slug', $role->slug) }}" required>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5>Hak Akses Izin (Permissions)</h5>
                    <p class="text-muted">Pilih izin yang akan diberikan untuk peran ini.</p>

                    @php
                        // Ambil permission yang sudah dimiliki role ini untuk pre-check checkbox
                        $rolePermissions = old('permissions', $role->permissions->pluck('id')->toArray());
                    @endphp

                    <div class="row mt-3">
                        @foreach ($menus as $menu)
                            <div class="col-12 mb-4">
                                <h6 class="mb-2 text-primary fw-bold">{{ $menu->name }}</h6>
                                @if ($menu->children->isNotEmpty())
                                    @foreach ($menu->children->sortBy('order') as $child)
                                        <div class="ms-3 mt-3">
                                            <strong class="d-block mb-2">{{ $child->name }}</strong>
                                            <div class="d-flex flex-wrap">
                                                @foreach ($child->permissions as $permission)
                                                    <div class="form-check form-check-inline me-3">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                                            value="{{ $permission->id }}" id="perm-{{ $permission->id }}"
                                                            {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="d-flex flex-wrap">
                                        @foreach ($menu->permissions as $permission)
                                            <div class="form-check form-check-inline me-3">
                                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                                    value="{{ $permission->id }}" id="perm-{{ $permission->id }}"
                                                    {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <hr class="mt-3">
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('roles.index') }}" class="btn btn-light">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
