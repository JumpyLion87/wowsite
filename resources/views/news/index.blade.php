@extends('layouts.app')

@section('title', __('news.page_title_list'))

@section('content')
<div class="main-content">
    <div class="wow-news-container">
        <h1 class="wow-news-title">{{ __('news.page_title_list') }}</h1>
        
        @if($newsList->isEmpty())
            <div class="no-news">{{ __('news.no_news') }}</div>
        @else
            <div class="news-list">
                @foreach($newsList as $news)
                    <a href="{{ route('news.index', ['slug' => $news->slug]) }}" class="news-link">
                        <article class="news-item {{ $news->is_important ? 'important' : '' }}">
                            @if(!empty($news->image_url))
                                <img src="{{ asset($news->image_url) }}"
                                     alt="{{ $news->title }}" 
                                     class="news-image"
                                     onerror="this.src='{{ asset($default_image_url) }}'">
                            @else
                                <img src="{{ asset($default_image_url) }}"
                                     alt="{{ $news->title }}" 
                                     class="news-image">
                            @endif
                            <div class="news-content">
                                <h2>{{ $news->title }}</h2>
                                <div class="news-meta">
                                    <span class="category {{ $news->category }}">
                                        {{ trans()->has('news.category_' . $news->category) ? __('news.category_' . $news->category) : ucfirst($news->category) }}
                                    </span>
                                    <span class="date">{{ $news->post_date->format('M j, Y') }}</span>
                                    <span class="author">{{ __('news.posted_by', ['author' => $news->posted_by]) }}</span>
                                </div>
                                <p class="news-excerpt">{{ $news->excerpt }}...</p>
                            </div>
                        </article>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($total_pages > 1)
                <div class="news-pagination">
                    @if($current_page > 1)
                        <a href="{{ route('news.index', ['page' => $current_page - 1]) }}"
                           class="pagination-link" 
                           aria-label="{{ __('news.pagination_previous') }}">« {{ __('news.pagination_prev') }}</a>
                    @endif
                    
                    @for($i = 1; $i <= $total_pages; $i++)
                        <a href="{{ route('news.index', ['page' => $i]) }}"
                           class="pagination-link {{ $i == $current_page ? 'active' : '' }}"
                           aria-label="{{ __('news.pagination_page', ['page' => $i]) }}">{{ $i }}</a>
                    @endfor
                    
                    @if($current_page < $total_pages)
                        <a href="{{ route('news.index', ['page' => $current_page + 1]) }}"
                           class="pagination-link" 
                           aria-label="{{ __('news.pagination_next') }}">{{ __('news.pagination_next') }} »</a>
                    @endif
                </div>
            @endif
        @endif
    </div>
</div>
@endsection