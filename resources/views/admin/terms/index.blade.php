@extends('admin.layout')

@section('title', 'Terms')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Terms</h2>
    <a href="{{ route('admin.terms.create') }}" class="btn btn-primary">Add Term</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Term Name</th>
            <th>Term Number</th>
        </tr>
    </thead>
    <tbody>
        @foreach($terms as $term)
        <tr>
            <td>{{ $term->term_name }}</td>
            <td>{{ $term->term_number }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
