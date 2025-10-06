@extends('layouts.app')

@section('title', $news->title)

@section('content')
<div class="dashboard-container">
    <div class="admin-header">
        <div class="admin-header-content">
            <h1 class="admin-title">
                <i class="fas fa-newspaper me-2"></i>
                {{ $news->title }}
            </h1>
            <p class="admin-subtitle">
                {{ __('admin_news.by') }} {{ $news->posted_by }} • 
                {{ $news->post_date->format('d.m.Y H:i') }}
            </p>
        </div>
        <div class="admin-header-actions">
            <a href="{{ route('admin.news.edit', $news->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>
                {{ __('admin_news.edit') }}
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
                <div class="news-meta">
                    <div class="news-meta-item">
                        <span class="meta-label">{{ __('admin_news.category') }}:</span>
                        <span class="category-badge category-{{ $news->category }}">
                            {{ __('admin_news.categories.' . $news->category) }}
                        </span>
                    </div>
                    
                    <div class="news-meta-item">
                        <span class="meta-label">{{ __('admin_news.author') }}:</span>
                        <span class="meta-value">{{ $news->posted_by }}</span>
                    </div>
                    
                    <div class="news-meta-item">
                        <span class="meta-label">{{ __('admin_news.publish_date') }}:</span>
                        <span class="meta-value">{{ $news->post_date->format('d.m.Y H:i') }}</span>
                    </div>
                    
                    <div class="news-meta-item">
                        <span class="meta-label">{{ __('admin_news.importance') }}:</span>
                        @if($news->is_important)
                            <span class="badge badge-warning">
                                <i class="fas fa-star me-1"></i>
                                {{ __('admin_news.important') }}
                            </span>
                        @else
                            <span class="badge badge-secondary">
                                {{ __('admin_news.regular') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                @if($news->image_url)
                    <div class="news-image-container">
                        <img src="{{ asset($news->image_url) }}" 
                             alt="{{ $news->title }}" 
                             class="news-image-large"
                             onerror="this.src='{{ asset('img/newsimg/news.png') }}'">
                    </div>
                @endif
                
                <div class="news-content">
                    {!! $news->content !!}
                </div>
                
                <div class="news-actions">
                    <a href="{{ route('admin.news.edit', $news->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('admin_news.edit') }}
                    </a>
                    
                    <form action="{{ route('admin.news.destroy', $news->id) }}" 
                          method="POST" class="d-inline" 
                          onsubmit="return confirm('{{ __('admin_news.delete_confirm') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            {{ __('admin_news.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/news.css') }}">
@endpush
