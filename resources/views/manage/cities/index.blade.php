@extends('manage.layouts.app')
@section('title', __('manage.cities'))

@section('content')
    <div class="mb-3">
        <a href="{{ route('manage.cities.create') }}" class="btn btn-success">
            <i class="bx bx-plus me-1"></i> {{ __('manage.create') }}
        </a>
    </div>

    <table class="table table-bordered" id="citiesTable">
        <thead>
            <tr>
                <th>{{ __('manage.id') }}</th>
                <th>{{ __('manage.active') }}</th>
                <th>{{ __('manage.name_ar') }}</th>
                <th>{{ __('manage.name_en') }}</th>
                <th>{{ __('manage.action') }}</th>
            </tr>
        </thead>
    </table>

    @include('manage.partials.delete-modal')
@endsection

@push('scripts')
<script>
    $('#citiesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('manage.cities.index') }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'is_active', name: 'is_active', orderable: true, searchable: false },
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

    let cityIdToDelete = null;

    $('#citiesTable').on('click', '.dropdown-item', function() {
        cityIdToDelete = $(this).data('id');
    });

    $('#confirmDelete').click(function() {
        if (cityIdToDelete) {
            $.ajax({
                url: '/manage/cities/' + cityIdToDelete,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    $('#citiesTable').DataTable().ajax.reload();
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
