@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Canva Designs</h2>
        <a href="{{ route('canva.create') }}" class="btn btn-primary">Add New Design</a>
    </div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Canva Link</th>
                        <th>Download Link</th>
                        <th>Expiry Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($designs as $design)
                        <tr>
                            <td>{{ $design->id }}</td>
                            <td><a href="{{ $design->canva_link }}" target="_blank">View</a></td>
                            <td>
                                <a href="{{ route('canva.download', $design->download_link) }}" class="btn btn-success btn-sm">Download PDF</a>
                            </td>
                            <td>{{ $design->expiry_date }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No designs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 