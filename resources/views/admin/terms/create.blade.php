@extends('admin.layout')

@section('title', 'Add Term')

@section('content')
<h2>Add Term</h2>

<form action="{{ route('admin.terms.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="term_name" class="form-label">Term Name</label>
                <input type="text" class="form-control" id="term_name" name="term_name" required>
            </div>
            <div class="mb-3">
                <label for="term_number" class="form-label">Term Number</label>
                <input type="number" class="form-control" id="term_number" name="term_number" min="1" required>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Create Term</button>
</form>
@endsection
