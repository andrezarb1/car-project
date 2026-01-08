@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">{{ $car->make }} {{ $car->model }} ({{ $car->year }})</h2>
        <div class="text-muted">{{ $car->slug }}</div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('cars.index') }}">Back</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <p><strong>Dealer:</strong> {{ $car->dealer->name ?? '-' }}</p>
        <p><strong>Fuel Type:</strong> {{ $car->fuel_type ?? '-' }}</p>
        <p><strong>Price:</strong> €{{ number_format($car->price, 2) }}</p>
        <p><strong>VIN:</strong> {{ $car->vin }}</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Images</h5>

        @if($car->images->count() === 0)
            <p class="text-muted mb-0">No images uploaded for this car yet.</p>
        @else
            <div class="row g-3">
                @foreach($car->images as $img)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ asset('storage/' . $img->image_path) }}" target="_blank" class="text-decoration-none">
                            <img
                                src="{{ asset('storage/' . $img->image_path) }}"
                                alt="Car image"
                                class="img-fluid rounded border"
                                style="height: 180px; width: 100%; object-fit: cover;"
                            >
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
