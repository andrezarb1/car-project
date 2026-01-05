@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">{{ $dealer->name }}</h2>
    <a class="btn btn-outline-secondary" href="{{ route('dealers.index') }}">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <p><strong>Slug:</strong> {{ $dealer->slug }}</p>
        <p><strong>Location:</strong> {{ $dealer->location ?? '-' }}</p>
        <p><strong>Email:</strong> {{ $dealer->email ?? '-' }}</p>
    </div>
</div>
@endsection
