@extends('manage.layouts.app')
@section('title',  __('manage.create') . ' ' . __('manage.city'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>{{ __('manage.create') }} {{ __('manage.city') }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('manage.cities.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Arabic Name -->
                <div class="mb-3">
                    <label for="name_ar" class="form-label">{{ __('manage.name_ar') }}</label>
                    <input type="text" class="form-control" id="name_ar" name="ar[name]" value="{{ old('ar.name') }}" placeholder="اسم المدينة باللغة العربية" required>
                    @error('ar.name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- English Name -->
                <div class="mb-3">
                    <label for="name_en" class="form-label">{{ __('manage.name_en') }}</label>
                    <input type="text" class="form-control" id="name_en" name="en[name]" value="{{ old('en.name') }}" placeholder="City Name in English" required>
                    @error('en.name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="mb-3">
                    <label for="is_active" class="form-label">{{ __('manage.active') }}</label>
                    <select class="form-control" id="is_active" name="is_active">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>{{ __('manage.active') }}</option>
                        <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>{{ __('manage.inactive') }}</option>
                    </select>
                    @error('is_active')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit and Cancel Buttons -->
                <button type="submit" class="btn btn-primary">{{ __('manage.save') }}</button>
                <a href="{{ route('manage.cities.index') }}" class="btn btn-secondary">{{ __('manage.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
