@extends('manage.layouts.app')
@section('title', __('manage.create') . ' ' . __('manage.district'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>{{ __('manage.create') }} {{ __('manage.district') }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('manage.districts.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Arabic Name -->
                <div class="mb-3">
                    <label for="name_ar" class="form-label">{{ __('manage.name_ar') }}</label>
                    <input type="text" class="form-control" id="name_ar" name="ar[name]" value="{{ old('ar.name') }}" placeholder="اسم الحي باللغة العربية" required>
                    @error('ar.name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- English Name -->
                <div class="mb-3">
                    <label for="name_en" class="form-label">{{ __('manage.name_en') }}</label>
                    <input type="text" class="form-control" id="name_en" name="en[name]" value="{{ old('en.name') }}" placeholder="District Name in English" required>
                    @error('en.name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Select City -->
                <div class="mb-3">
                    <label for="city_id" class="form-label">{{ __('manage.city') }}</label>
                    <select class="form-control" id="city_id" name="city_id" required>
                        <option value="" disabled selected>{{ __('manage.city') }}</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('city_id')
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
                <a href="{{ route('manage.districts.index') }}" class="btn btn-secondary">{{ __('manage.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
