@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Cars</h2>
    <a class="btn btn-primary" href="{{ route('cars.create') }}">+ Add Car</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Make</label>
                <select name="make" class="form-select">
                    <option value="">All</option>
                    @foreach($makes as $make)
                        <option value="{{ $make }}" {{ request('make') === $make ? 'selected' : '' }}>
                            {{ $make }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Fuel Type</label>
                <select name="fuel_type" class="form-select">
                    <option value="">All</option>
                    @foreach($fuelTypes as $ft)
                        <option value="{{ $ft }}" {{ request('fuel_type') === $ft ? 'selected' : '' }}>
                            {{ $ft }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-dark" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="{{ route('cars.index') }}">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($cars->count() === 0)
            <p class="text-muted mb-0">No cars found.</p>
        @else
            <div class="d-flex justify-content-end mb-2">
                <div class="btn-group">
                    <a class="btn btn-outline-secondary btn-sm"
                       href="{{ route('cars.index', array_merge(request()->query(), ['sort'=>'price','dir'=> ($sort==='price' && $dir==='asc') ? 'desc':'asc'])) }}">
                        Sort: Price
                    </a>
                    <a class="btn btn-outline-secondary btn-sm"
                       href="{{ route('cars.index', array_merge(request()->query(), ['sort'=>'year','dir'=> ($sort==='year' && $dir==='asc') ? 'desc':'asc'])) }}">
                        Sort: Year
                    </a>
                </div>
            </div>

            <table class="table table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Car</th>
                    <th>Dealer</th>
                    <th>Year</th>
                    <th>Fuel</th>
                    <th>Price</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($cars as $car)
                    <tr>
                        <td>
                            <strong>{{ $car->make }} {{ $car->model }}</strong>
                            <div class="text-muted small">{{ $car->slug }}</div>
                        </td>
                        <td>{{ $car->dealer->name ?? '-' }}</td>
                        <td>{{ $car->year }}</td>
                        <td>{{ $car->fuel_type ?? '-' }}</td>
                        <td>€{{ number_format($car->price, 2) }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('cars.show', $car) }}">View</a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('cars.edit', $car) }}">Edit</a>

                            <form action="{{ route('cars.destroy', $car) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this car?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $cars->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
