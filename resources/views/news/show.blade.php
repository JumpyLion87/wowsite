@extends('layouts.app')

@section('title', $news->title)

@section('content')
<div class="main-content">
    <div class="wow-news-container">
        <article class="news-single">
            <div class="news-header">
                <h1 class="news-title">{{ $news->title }}</h1>
                <div class="news-meta">
                    <span class="category {{ $news->category }}">
                        {{ trans()->has('news.category_' . $news->category) ? __('news.category_' . $news->category) : ucfirst($news->category) }}
                    </span>
                    <span class="date">{{ $news->post_date->format('F j, Y') }}</span>
                    <span class="author">{{ __('news.posted_by', ['author' => $news->posted_by]) }}</span>
                </div>
            </div>

            @if(!empty($news->image_url))
                <div class="news-image-container">
                    <img src="{{ asset($news->image_url) }}" 
                         alt="{{ $news->title }}" 
                         class="news-image-large"
                         onerror="this.src='{{ asset($default_image_url) }}'">
                </div>
            @else
                <div class="news-image-container">
                    <img src="{{ asset($default_image_url) }}" 
                         alt="{{ $news->title }}" 
                         class="news-image-large">
                </div>
            @endif

            <div class="news-content">
                {!! $news->content !!}
            </div>

            <div class="news-footer">
                <div class="news-navigation">
                    <a href="{{ route('news.index') }}" class="btn btn-primary">
                        « {{ __('news.back_to_list') }}
                    </a>
                </div>
                
                @if($news->is_important)
                    <div class="news-important-badge">
                        <span class="badge badge-warning">{{ __('news.important_news') }}</span>
                    </div>
                @endif
            </div>
        </article>
    </div>
</div>
@endsection
