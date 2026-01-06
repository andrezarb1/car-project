@extends('layout')

@section('content')
<h2 class="mb-3">Add Car</h2>

<div class="card">
    <div class="card-body">
        <form action="{{ route('cars.store') }}" method="POST">
            @csrf
            @include('cars._form', ['car' => new \App\Models\Car(), 'dealers' => $dealers])
            <button class="btn btn-primary" type="submit">Create</button>
            <a class="btn btn-outline-secondary" href="{{ route('cars.index') }}">Cancel</a>
        </form>
    </div>
</div>
@endsection
