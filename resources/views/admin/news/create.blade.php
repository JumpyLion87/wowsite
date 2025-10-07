@extends('layouts.app')

@section('title', __('admin_news.create_news'))

@section('content')
<div class="dashboard-container">
    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="admin-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ \App\Helpers\DashboardHelper::getDashboardRoute() }}">
                    <i class="{{ \App\Helpers\DashboardHelper::getDashboardIcon() }} me-1"></i>
                    {{ \App\Helpers\DashboardHelper::getDashboardTitle() }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.news.index') }}">
                    <i class="fas fa-newspaper me-1"></i>
                    {{ __('admin_news.news_management') }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-plus me-1"></i>
                {{ __('admin_news.create_news') }}
            </li>
        </ol>
    </nav>

    <div class="admin-header">
        <div class="admin-header-content">
            <h1 class="admin-title">
                <i class="fas fa-plus me-2"></i>
                {{ __('admin_news.create_news') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_news.create_news_description') }}</p>
        </div>
        <div class="admin-header-actions">
            <a href="{{ \App\Helpers\DashboardHelper::getDashboardRoute() }}" class="btn btn-secondary me-2">
                <i class="{{ \App\Helpers\DashboardHelper::getDashboardIcon() }} me-2"></i>
                {{ \App\Helpers\DashboardHelper::getDashboardTitle() }}
            </a>
            <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_news.back_to_list') }}
            </a>
        </div>
    </div>

    <div class="admin-content">
        <div class="content-card">
            <div class="content-header">
                <h3>{{ __('admin_news.news_form') }}</h3>
            </div>
            
            <div class="content-body">
                <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data" class="news-form">
                    @csrf
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1"></i>
                                {{ __('admin_news.title') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="title" name="title" 
                                   value="{{ old('title') }}" 
                                   class="form-control @error('title') is-invalid @enderror"
                                   placeholder="{{ __('admin_news.title_placeholder') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="category" class="form-label">
                                <i class="fas fa-tag me-1"></i>
                                {{ __('admin_news.category') }} <span class="text-danger">*</span>
                            </label>
                            <select id="category" name="category" 
                                    class="form-control @error('category') is-invalid @enderror" required>
                                <option value="">{{ __('admin_news.select_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                        {{ __('admin_news.categories.' . $category) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="image_url" class="form-label">
                                <i class="fas fa-link me-1"></i>
                                {{ __('admin_news.image_url') }}
                            </label>
                            <input type="text" id="image_url" name="image_url" 
                                   value="{{ old('image_url') }}" 
                                   class="form-control @error('image_url') is-invalid @enderror"
                                   placeholder="{{ __('admin_news.image_url_placeholder') }}">
                            @error('image_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="image_file" class="form-label">
                                <i class="fas fa-upload me-1"></i>
                                {{ __('admin_news.image_upload') }}
                            </label>
                            <input type="file" id="image_file" name="image_file" accept="image/*"
                                   class="form-control @error('image_file') is-invalid @enderror">
                            @error('image_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text">{{ __('admin_news.image_upload_hint') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="post_date" class="form-label">
                                <i class="fas fa-calendar me-1"></i>
                                {{ __('admin_news.publish_date') }} <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" id="post_date" name="post_date" 
                                   value="{{ old('post_date', now()->format('Y-m-d\TH:i')) }}" 
                                   class="form-control @error('post_date') is-invalid @enderror" required>
                            @error('post_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="content" class="form-label">
                            <i class="fas fa-align-left me-1"></i>
                            {{ __('admin_news.content') }} <span class="text-danger">*</span>
                        </label>
                        <textarea id="content" name="content" 
                                  class="form-control @error('content') is-invalid @enderror" 
                                  rows="10" required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="is_important" name="is_important" 
                                   class="form-check-input" {{ old('is_important') ? 'checked' : '' }}>
                            <label for="is_important" class="form-check-label">
                                <i class="fas fa-star me-1"></i>
                                {{ __('admin_news.mark_important') }}
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <i class="fas fa-save me-2"></i>
                            {{ __('admin_news.create_news') }}
                        </button>
                        <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>
                            {{ __('admin_news.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing CKEditor...');
    
    const submitBtn = document.getElementById('submit-btn');
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            console.log('Submit button clicked');
            if (window.ckeditor) {
                console.log('Syncing CKEditor data before submit');
                
                // Принудительно обновляем textarea
                const textarea = document.querySelector('#content');
                if (textarea) {
                    const editorData = window.ckeditor.getData();
                    textarea.value = editorData;
                    console.log('Textarea updated with:', textarea.value);
                    console.log('Textarea value length:', textarea.value.length);
                }
                
                // Также пробуем updateSourceElement
                window.ckeditor.updateSourceElement();
                console.log('CKEditor data after sync:', window.ckeditor.getData());
            }
        });
    }
    
    ClassicEditor
        .create(document.querySelector('#content'), {
            toolbar: [
                'heading', '|',
                'bold', 'italic', '|',
                'bulletedList', 'numberedList', '|',
                'link', '|',
                'undo', 'redo'
            ],
            language: '{{ app()->getLocale() }}'
        })
        .then(editor => {
            console.log('CKEditor initialized successfully');
            window.ckeditor = editor; // Сохраняем ссылку на редактор
            
            // Синхронизируем данные CK Editor с формой перед отправкой
            const form = document.querySelector('.news-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submit triggered');
                    
                    // Принудительно обновляем textarea с данными из CKEditor
                    const textarea = document.querySelector('#content');
                    if (textarea) {
                        const editorData = editor.getData();
                        textarea.value = editorData;
                        console.log('Textarea value set to:', textarea.value);
                        console.log('Textarea value length:', textarea.value.length);
                    }
                    
                    // Также пробуем updateSourceElement
                    editor.updateSourceElement();
                    console.log('CKEditor data synced:', editor.getData());
                });
            }
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });
});
</script>
@endpush
