@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Dealers</h2>
    <a class="btn btn-primary" href="{{ route('dealers.create') }}">+ Add Dealer</a>
</div>

<div class="card">
    <div class="card-body">
        @if($dealers->count() === 0)
            <p class="text-muted mb-0">No dealers yet. Click “Add Dealer” to create one.</p>
        @else
            <table class="table table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>
                        <a href="{{ route('dealers.index', ['sort' => 'name', 'dir' => ($sort === 'name' && $dir === 'asc') ? 'desc' : 'asc']) }}"
                           class="text-decoration-none">
                            Name
                        </a>
                    </th>
                    <th>Location</th>
                    <th>Email</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($dealers as $dealer)
                    <tr>
                        <td>
                            <strong>{{ $dealer->name }}</strong>
                            <div class="text-muted small">{{ $dealer->slug }}</div>
                        </td>
                        <td>{{ $dealer->location ?? '-' }}</td>
                        <td>{{ $dealer->email ?? '-' }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('dealers.show', $dealer) }}">View</a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('dealers.edit', $dealer) }}">Edit</a>

                            <form action="{{ route('dealers.destroy', $dealer) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this dealer?');">
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
                {{ $dealers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
