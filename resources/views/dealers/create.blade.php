@extends('layout')

@section('content')
<h2 class="mb-3">Add Dealer</h2>

<div class="card">
    <div class="card-body">
        <form action="{{ route('dealers.store') }}" method="POST">
            @csrf
            @include('dealers._form', ['dealer' => new \App\Models\Dealer()])
            <button class="btn btn-primary" type="submit">Create</button>
            <a class="btn btn-outline-secondary" href="{{ route('dealers.index') }}">Cancel</a>
        </form>
    </div>
</div>
@endsection
