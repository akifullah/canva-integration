@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="alert alert-success">
        <h4 class="alert-heading">Canva OAuth Success!</h4>
        <p>Your Canva access token is:</p>
        <code class="d-block p-2 bg-light">{{ $access_token }}</code>
        <p class="mt-3">You can now use this token to call the Canva API.</p>
    </div>
</div>
@endsection 