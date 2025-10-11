@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <h3>Audit Log</h3>
        <p class="text-subtitle text-muted">Rekam jejak semua aktivitas di sistem.</p>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Aktivitas</th>
                                <th>Objek</th>
                                <th class="text-center">Detail Perubahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activities as $activity)
                                <tr>
                                    <td>{{ $activity->created_at->format('d M Y, H:i') }}</td>
                                    <td>{{ $activity->causer->name ?? 'Sistem' }}</td>
                                    <td>{{ $activity->description }}</td>
                                    <td>
                                        <span class="badge bg-light-secondary">{{ $activity->log_name }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($activity->properties->has('old') || $activity->properties->has('attributes'))
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal-{{ $activity->id }}">
                                                Lihat
                                            </button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada aktivitas tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $activities->links('components.pagination') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk Detail Perubahan --}}
    @foreach ($activities as $activity)
    <div class="modal fade" id="detailModal-{{ $activity->id }}" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Perubahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @if($activity->properties->has('old'))
                        <div class="col-md-6">
                            <h6>Data Lama</h6>
                            <pre>{{ json_encode($activity->properties['old'], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif
                        @if($activity->properties->has('attributes'))
                        <div class="col-md-6">
                            <h6>Data Baru</h6>
                            <pre>{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endsection
