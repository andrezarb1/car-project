@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">{{ $car->make }} {{ $car->model }} ({{ $car->year }})</h2>
        <div class="text-muted">{{ $car->slug }}</div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('cars.index') }}">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <p><strong>Dealer:</strong> {{ $car->dealer->name ?? '-' }}</p>
        <p><strong>Fuel Type:</strong> {{ $car->fuel_type ?? '-' }}</p>
        <p><strong>Price:</strong> €{{ number_format($car->price, 2) }}</p>
        <p><strong>VIN:</strong> {{ $car->vin }}</p>
    </div>
</div>
@endsection
