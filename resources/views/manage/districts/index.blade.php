@extends('manage.layouts.app')
@section('title', __('manage.districts'))

@section('content')
    <div class="mb-3">
        <a href="{{ route('manage.districts.create') }}" class="btn btn-success">
            <i class="bx bx-plus me-1"></i> {{ __('manage.create') }}
        </a>
    </div>
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="filter-city" class="form-label">{{ __('manage.filter_by_city') }}</label>
            <select id="filter-city" class="form-select">
                <option value="">{{ __('manage.cities') }}</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <table class="table table-bordered" id="districtsTable">
        <thead>
            <tr>
                <th>{{ __('manage.id') }}</th>
                <th>{{ __('manage.active') }}</th>
                <th>{{ __('manage.city') }}</th>
                <th>{{ __('manage.name_ar') }}</th>
                <th>{{ __('manage.name_en') }}</th>
                <th>{{ __('manage.action') }}</th>
            </tr>
        </thead>

        <tbody>
        </tbody>
    </table>
    



    @include('manage.partials.delete-modal')
@endsection

@push('scripts')
<script>
    var table = $('#districtsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('manage.districts.index') }}',
            data: function(d) {
                d.city_id = $('#filter-city').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'is_active', name: 'is_active', orderable: true, searchable: false },
            { data: 'city_name', name: 'city_name' },
            { data: 'name_ar', name: 'name_ar' },
            { data: 'name_en', name: 'name_en' },
            { data: 'action', name: 'action', orderable: false, searchable: false } 
        ],
        language: {
            "processing": "{{ __('manage.processing') }}",
            "lengthMenu": "{{ __('manage.lengthMenu') }}",
            "zeroRecords": "{{ __('manage.zeroRecords') }}",
            "info": "{{ __('manage.info') }}",
            "infoEmpty": "{{ __('manage.infoEmpty') }}",
            "infoFiltered": "{{ __('manage.infoFiltered') }}",
            "search": "{{ __('manage.search') }}"
        }
    });

    $('#filter-city').on('change', function() {
        table.ajax.reload();
    });

    let districtIdToDelete = null;

    $('#districtsTable').on('click', '.dropdown-item', function() {
        districtIdToDelete = $(this).data('id');
    });

    $('#confirmDelete').click(function() {
        if (districtIdToDelete) {
            $.ajax({
                url: '/manage/districts/' + districtIdToDelete,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    table.ajax.reload();  
                    $('#deleteModal').modal('hide');
                    Toastify({
                        text: response.message,
                        duration: 3000,
                        gravity: "top",
                        position: "center",
                        style: { background: "linear-gradient(to right, #00b09b, #96c93d)" },
                    }).showToast();
                },
                error: function(xhr) {
                    Toastify({
                        text: "{{ __('manage.error') }}",
                        duration: 3000,
                        gravity: "top",
                        position: "center",
                        style: { background: "linear-gradient(to right, #e63946, #f1faee)" },
                    }).showToast();
                    $('#deleteModal').modal('hide');
                }
            });
        }
    });
</script>
@endpush
