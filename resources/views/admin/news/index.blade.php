@extends('layouts.app')

@section('title', __('admin_news.news_management'))

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
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-newspaper me-1"></i>
                {{ __('admin_news.news_management') }}
            </li>
        </ol>
    </nav>

    <div class="admin-header">
        <div class="admin-header-content">
            <h1 class="admin-title">
                <i class="fas fa-newspaper me-2"></i>
                {{ __('admin_news.news_management') }}
            </h1>
            <p class="admin-subtitle">{{ __('admin_news.news_management_description') }}</p>
        </div>
        <div class="admin-header-actions">
            <a href="{{ \App\Helpers\DashboardHelper::getDashboardRoute() }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>
                {{ __('admin_dashboard.back_to_dashboard') }}
            </a>
            <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                {{ __('admin_news.create_news') }}
            </a>
        </div>
    </div>

    <!-- Фильтры и поиск -->
    <div class="admin-filters">
        <form method="GET" action="{{ route('admin.news.index') }}" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search">{{ __('admin_news.search') }}:</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="{{ __('admin_news.search_placeholder') }}" class="form-control">
                </div>
                
                <div class="filter-group">
                    <label for="category">{{ __('admin_news.category') }}:</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">{{ __('admin_news.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ __('admin_news.categories.' . $category) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="important">{{ __('admin_news.importance') }}:</label>
                    <select id="important" name="important" class="form-control">
                        <option value="">{{ __('admin_news.all_news') }}</option>
                        <option value="1" {{ request('important') == '1' ? 'selected' : '' }}>
                            {{ __('admin_news.important_only') }}
                        </option>
                        <option value="0" {{ request('important') == '0' ? 'selected' : '' }}>
                            {{ __('admin_news.regular_only') }}
                        </option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        {{ __('admin_news.filter') }}
                    </button>
                    <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>
                        {{ __('admin_news.clear') }}
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Список новостей -->
    <div class="admin-content">
        <div class="content-card">
            <div class="content-header">
                <h3>{{ __('admin_news.news_list') }}</h3>
                <span class="badge badge-info">{{ $news->total() }} {{ __('admin_news.news_count') }}</span>
            </div>
            
            <div class="content-body">
                @if($news->count() > 0)
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>{{ __('admin_news.title') }}</th>
                                    <th>{{ __('admin_news.category') }}</th>
                                    <th>{{ __('admin_news.author') }}</th>
                                    <th>{{ __('admin_news.date') }}</th>
                                    <th>{{ __('admin_news.importance') }}</th>
                                    <th>{{ __('admin_news.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($news as $item)
                                    <tr>
                                        <td class="news-title-cell">
                                            <div class="news-info">
                                                <span class="news-title">{{ $item->title }}</span>
                                                @if($item->image_url)
                                                    <i class="fas fa-image text-muted ms-1" title="{{ __('admin_news.has_image') }}"></i>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="category-badge category-{{ $item->category }}">
                                                {{ __('admin_news.categories.' . $item->category) }}
                                            </span>
                                        </td>
                                        <td>{{ $item->posted_by }}</td>
                                        <td>{{ $item->post_date->format('d.m.Y H:i') }}</td>
                                        <td>
                                            @if($item->is_important)
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-star me-1"></i>
                                                    {{ __('admin_news.important') }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    {{ __('admin_news.regular') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="action-cell">
                                            <div class="action-buttons">
                                                <a href="{{ route('admin.news.show', $item->id) }}" 
                                                   class="btn btn-sm btn-info" title="{{ __('admin_news.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.news.edit', $item->id) }}" 
                                                   class="btn btn-sm btn-warning" title="{{ __('admin_news.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.news.destroy', $item->id) }}" 
                                                      method="POST" class="d-inline" 
                                                      onsubmit="return confirm('{{ __('admin_news.delete_confirm') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="{{ __('admin_news.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Пагинация -->
                    @if($news->hasPages())
                        <div class="pagination-container">
                            {{ $news->appends(request()->query())->links('pagination.admin-pagination') }}
                        </div>
                    @endif
                @else
                    <div class="no-data">
                        <i class="fas fa-newspaper fa-3x mb-3"></i>
                        <h4>{{ __('admin_news.no_news') }}</h4>
                        <p>{{ __('admin_news.no_news_description') }}</p>
                        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            {{ __('admin_news.create_first_news') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
