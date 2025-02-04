@extends('manage.layouts.app')
@section('title',  __('manage.create') . ' ' . __('manage.splash_screen'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>{{ __('manage.create') }} {{ __('manage.splash_screen') }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('manage.splash-screens.store') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label for="title" class="form-label">{{ __('manage.title') }}</label>
                    
                    <input type="text" class="form-control" id="title_ar" name="ar[title]" value="{{ old('ar.title') }}" placeholder="العنوان باللغة العربية" required>
                    @error('ar.title')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    
                    <input type="text" class="form-control mt-2" id="title_en" name="en[title]" value="{{ old('en.title') }}" placeholder="English Title" required>
                    @error('en.title')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('manage.description') }}</label>
                    
                    <textarea class="form-control" id="description_ar" name="ar[description]" rows="3" placeholder="الوصف باللغة العربية" required>{{ old('ar.description') }}</textarea>
                    @error('ar.description')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    
                    <textarea class="form-control mt-2" id="description_en" name="en[description]" rows="3" placeholder="English Description" required>{{ old('en.description') }}</textarea>
                    @error('en.description')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">{{ __('manage.image') }}</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                    @error('image')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="is_active" class="form-label">{{ __('manage.active') }}</label>
                    <select class="form-control" id="is_active" name="is_active">
                        <option value="1" {{ old('is_active') == 1 ? 'selected' : '' }}>{{ __('manage.active') }}</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>{{ __('manage.inactive') }}</option>
                    </select>
                    @error('is_active')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">{{ __('manage.save') }}</button>
                <a href="{{ route('manage.splash-screens.index') }}" class="btn btn-secondary">{{ __('manage.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
