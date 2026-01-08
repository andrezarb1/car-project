@extends('layout')

@section('content')
<h2 class="mb-3">Edit Car</h2>

<div class="card">
    <div class="card-body">
        <form action="{{ route('cars.update', $car) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('cars._form', ['car' => $car, 'dealers' => $dealers])
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-outline-secondary" href="{{ route('cars.index') }}">Cancel</a>
        </form>
    </div>
</div>
@endsection
