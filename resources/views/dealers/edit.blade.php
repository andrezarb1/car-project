@extends('layout')

@section('content')
<h2 class="mb-3">Edit Dealer</h2>

<div class="card">
    <div class="card-body">
        <form action="{{ route('dealers.update', $dealer) }}" method="POST">
            @csrf
            @method('PUT')
            @include('dealers._form', ['dealer' => $dealer])
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-outline-secondary" href="{{ route('dealers.index') }}">Cancel</a>
        </form>
    </div>
</div>
@endsection
