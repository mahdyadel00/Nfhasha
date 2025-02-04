@extends('manage.layouts.app')
@section('title', __('manage.splash_screens'))

@section('content')
    <!-- زر لإضافة شاشة جديدة -->
    <div class="mb-3">
        <a href="{{ route('manage.splash-screens.create') }}" class="btn btn-success">
            <i class="bx bx-plus me-1"></i> {{ __('manage.create') }}
        </a>
    </div>

    <!-- جدول لعرض الشاشات -->
    <table class="table table-bordered" id="splashScreensTable">
        <thead>
            <tr>
                <th>{{ __('manage.id') }}</th>
                <th>{{ __('manage.active') }}</th>
                <th>{{ __('manage.image') }}</th>
                <th>{{ __('manage.title_ar') }}</th>
                <th>{{ __('manage.title_en') }}</th>
                <th>{{ __('manage.description_ar') }}</th>
                <th>{{ __('manage.description_en') }}</th>
                <th>{{ __('manage.action') }}</th>
            </tr>
        </thead>
    </table>

    @include('manage.partials.delete-modal')
    
@endsection

@push('scripts')
<script>
        $('#splashScreensTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('manage.splash-screens.index') }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'is_active', name: 'is_active', orderable: true, searchable: false },
                { data: 'image', name: 'image', orderable: false, searchable: false },
                { data: 'title_ar', name: 'title_ar' },
                { data: 'title_en', name: 'title_en' },
                { data: 'description_ar', name: 'description_ar' },
                { data: 'description_en', name: 'description_en' },
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


        //Deleting Records
        let splashScreenIdToDelete = null;

        $('#splashScreensTable').on('click', '.dropdown-item', function() {
            splashScreenIdToDelete = $(this).data('id');
        });


        $('#confirmDelete').click(function() {
            console.log('confirm delete');
            if (splashScreenIdToDelete) {
                $.ajax({
                    url: '/manage/splash-screens/' + splashScreenIdToDelete,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#splashScreensTable').DataTable().ajax.reload();
                        $('#deleteModal').modal('hide');
                        Toastify({
                            text: response.message,
                            duration: 3000,
                            gravity: "top", 
                            position: "center",
                            style: {
                                background: "linear-gradient(to right, #00b09b, #96c93d)", 
                            },
                            onClick: function(){} 
                        }).showToast();
                    },
                    error: function(xhr) {
                        Toastify({
                            text: "{{ __('manage.error') }}",
                            duration: 3000,
                            gravity: "top",
                            position: "center",
                            style: {
                                background: "linear-gradient(to right, #e63946, #f1faee)", 
                            },
                            onClick: function(){} 
                        }).showToast();
                        $('#deleteModal').modal('hide');
                    }
                });
            }
        });
</script>
@endpush
