@extends('layouts.app')

@section('content')
    <div class="container mt-5">


        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header text-white" style="background-color: #2170B6;">Add Canva Design</div>
                <div class="card-body py-2">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('canva.store') }}" class="mb-0">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="name" class="form-label mb-0">Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="canva_link" class="form-label mb-0">Canva Link</label>
                                    <input type="url" class="form-control" id="canva_link" name="canva_link"
                                        value="{{ old('canva_link') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label mb-0">Expiry Date</label>
                                    <input type="date" class="form-control" id="expiry_date" name="expiry_date"
                                        value="{{ old('expiry_date') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3 align-self-end">
                                <div class="mb-md-3 mb-0">
                                    <button type="submit" class="btn w-100"
                                        style="background-color: #F15A23; color: #fff; font-weight: 500;">Add
                                        Design</button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>


        {{-- <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Canva Designs</h2>
            <a href="{{ route('canva.create') }}" class="btn btn-primary">Add New Design</a>
        </div> --}}


        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="card responsive-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm table-hover mb-0 table-bordered align-middle rounded-table beautiful-table">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Canva Link</th>
                                <th>Download Link</th>
                                <th>Expiry Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($designs as $design)
                                <tr>
                                    <td>{{ $design->id }}</td>
                                    <td>{{ $design->name }}</td>
                                    <td><a href="{{ $design->canva_link }}" target="_blank">View</a></td>
                                    <td>
                                        @if ($design->is_expired)
                                            <span class="badge bg-danger" style="font-size: 1em;">Expired</span>
                                        @else
                                            <a href="{{ route('canva.download', ['link' => $design->download_link]) }}"
                                                class="btn btn-success btn-sm" title="Download PDF"><i class="bi bi-download"></i></a>
                                            <a href="{{ route('canva.preview', ['link' => $design->download_link]) }}" target="_blank"
                                                class="btn btn-primary btn-sm" title="Preview PDF"><i class="bi bi-eye"></i></a>
                                            <button class="btn btn-info btn-sm copy-link-btn" data-link="{{ route('canva.download', ['link' => $design->download_link]) }}" type="button" title="Copy Link"><i class="bi bi-clipboard"></i></button>
                                        @endif
                                    </td>
                                    <td>{{ $design->expiry_date ? $design->expiry_date->format('Y-m-d') : '' }}</td>
                                    <td>
                                        <a href="{{ route('canva.edit', $design->id) }}"
                                            class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('canva.destroy', $design->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
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
    </div>
@endsection
<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const copyButtons = document.querySelectorAll('.copy-link-btn');
        copyButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const link = btn.getAttribute('data-link');
                navigator.clipboard.writeText(link).then(function () {
                    const icon = btn.querySelector('i');
                    const originalClass = icon.className;
                    icon.className = 'bi bi-clipboard-check';
                    setTimeout(() => {
                        icon.className = originalClass;
                    }, 1500);
                }, function (err) {
                    alert('Failed to copy link');
                });
            });
        });
    });
</script>
<style>
    .responsive-card {
        border-radius: 1rem;
        box-shadow: 0 2px 16px rgba(33, 112, 182, 0.08);
        border: 1px solid #e3e6f0;
        overflow: hidden;
        margin-bottom: 30px;
    }
    .rounded-table {
        border-radius: 0.75rem;
        /* overflow: hidden; */
        
    }
    form{
        margin-bottom: 0;
    }
    table{
        min-width: 600px !important;
    }
    .beautiful-table{
        vertical-align: middle;
        min-width: 500px;
    }
    .beautiful-table thead th {
        /* background: linear-gradient(90deg, #2170B6 0%, #4e9fd6 100%); */
        color: #fff;
        font-weight: 600;
        border-bottom: 2.5px solid #1a4e7a;
    }
    .beautiful-table tbody tr {
        transition: background 0.2s;
    }
    .beautiful-table tbody tr:hover {
        background: #f0f6fa;
    }
    .beautiful-table td, .beautiful-table th {
        vertical-align: middle;
        padding-top: 0.45rem;
        padding-bottom: 0.45rem;
    }
    .beautiful-table td {
        background: #fff;
    }
    .beautiful-table .badge.bg-danger {
        font-size: 0.95em;
        padding: 0.45em 0.9em;
        letter-spacing: 0.03em;
    }
    @media (max-width: 768px) {
        .responsive-card {
            border-radius: 0.5rem;
            box-shadow: 0 1px 8px rgba(33, 112, 182, 0.10);
        }
        .rounded-table {
            border-radius: 0.5rem;
        }
        .table-responsive {
            border-radius: 0.5rem;
        }
        .beautiful-table td, .beautiful-table th {
            padding-top: 0.2rem;
            padding-bottom: 0.2rem;
            font-size: 0.85rem;
        }
        .beautiful-table {
            min-width: 320px;
            font-size: 0.85rem;
        }
        .beautiful-table .badge.bg-danger {
            font-size: 0.85em;
            padding: 0.3em 0.6em;
        }
    }
    .table-bordered > :not(caption) > * > * {
        border-width: 1.5px;
        border-color: #b6c6d6;
    }
</style>
