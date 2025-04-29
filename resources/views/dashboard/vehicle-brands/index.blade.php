@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <h1>Vehicle Brands</h1>
        <a href="{{ route('vehicle-brands.create') }}" class="btn btn-primary">Add New Vehicle Brand</a>
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vehicleBrands as $brand)
                    <tr>
                        <td>{{ $brand->id }}</td>
                        <td>{{ $brand->title }}</td>
                        <td>{{ $brand->status ? 'Active' : 'Inactive' }}</td>
                        <td>
                            <form action="{{ route('vehicle-brands.destroy', $brand) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
