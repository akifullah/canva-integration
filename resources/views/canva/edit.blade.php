@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">Edit Canva Design</div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('canva.update', $design->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $design->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="canva_link" class="form-label">Canva Link</label>
                            <input type="url" class="form-control" id="canva_link" name="canva_link" value="{{ old('canva_link', $design->canva_link) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="expiry_date" class="form-label">Expiration Date</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', $design->expiry_date) }}" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Update Design</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 