<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = News::query();

        // Поиск по заголовку
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Фильтр по категории
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Фильтр по важности
        if ($request->filled('important')) {
            $query->where('is_important', $request->important);
        }

        $news = $query->orderBy('post_date', 'desc')->paginate(10);
        
        $categories = ['update', 'event', 'maintenance', 'other'];
        
        return view('admin.news.index', compact('news', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ['update', 'event', 'maintenance', 'other'];
        return view('admin.news.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            \Log::info('News creation attempt', [
                'title' => $request->title,
                'content_length' => strlen($request->content ?? ''),
                'content_preview' => substr($request->content ?? '', 0, 100),
                'category' => $request->category,
                'has_file' => $request->hasFile('image_file'),
                'all_data' => $request->all()
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:100',
                'content' => 'required|string',
                'category' => 'required|in:update,event,maintenance,other',
                'image_url' => 'nullable|string|max:255',
                'image_file' => 'nullable|image|max:4096',
                'is_important' => 'nullable',
                'post_date' => 'required|date'
            ]);
            
            \Log::info('Validation passed', [
                'validated_data' => $validated
            ]);

        $news = new News();
        $news->title = $request->title;
        // Set slug only if column exists in the table
        if (Schema::hasColumn($news->getTable(), 'slug')) {
            $news->slug = Str::slug($request->title);
        }
        // Очищаем контент от двойного экранирования
        $news->content = html_entity_decode($request->content, ENT_QUOTES, 'UTF-8');
        $news->category = $request->category;
        // Image handling: uploaded file takes precedence over direct URL
        if ($request->hasFile('image_file')) {
            // Сохраняем в папку newsimg вместо storage
            $file = $request->file('image_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('img/newsimg'), $filename);
            $news->image_url = 'img/newsimg/' . $filename;
        } else {
            // Очищаем URL от полного домена, оставляем только относительный путь
            $imageUrl = $request->image_url;
            if ($imageUrl && (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'))) {
                $parsedUrl = parse_url($imageUrl);
                $imageUrl = $parsedUrl['path'] ?? $imageUrl;
            }
            $news->image_url = $imageUrl;
        }
        $news->is_important = $request->has('is_important');
        $news->post_date = $request->post_date;
        $news->posted_by = Auth::user()->username ?? 'Admin';
        
        $news->save();

        return redirect()->route('admin.news.index')
            ->with('success', __('admin_news.news_created_successfully'));
        } catch (\Exception $e) {
            \Log::error('Error creating news', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->withErrors(['error' => 'Произошла ошибка при создании новости: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        // Декодируем HTML контент для правильного отображения
        $news->content = html_entity_decode($news->content, ENT_QUOTES, 'UTF-8');
        
        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news)
    {
        $categories = ['update', 'event', 'maintenance', 'other'];
        
        // Декодируем HTML контент для правильного отображения в CKEditor
        $news->content = html_entity_decode($news->content, ENT_QUOTES, 'UTF-8');
        
        return view('admin.news.edit', compact('news', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        try {
            \Log::info('News update attempt', [
                'news_id' => $news->id,
                'title' => $request->title,
                'content_length' => strlen($request->content),
                'has_file' => $request->hasFile('image_file'),
                'all_data' => $request->all()
            ]);
            
            $request->validate([
            'title' => 'required|string|max:100',
            'content' => 'required|string',
            'category' => 'required|in:update,event,maintenance,other',
            'image_url' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|max:4096',
            'is_important' => 'nullable',
            'post_date' => 'required|date'
        ]);

        $news->title = $request->title;
        // Set slug only if column exists in the table
        if (Schema::hasColumn($news->getTable(), 'slug')) {
            $news->slug = Str::slug($request->title);
        }
        // Очищаем контент от двойного экранирования
        $news->content = html_entity_decode($request->content, ENT_QUOTES, 'UTF-8');
        $news->category = $request->category;
        if ($request->hasFile('image_file')) {
            // Сохраняем в папку newsimg вместо storage
            $file = $request->file('image_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('img/newsimg'), $filename);
            $news->image_url = 'img/newsimg/' . $filename;
        } else {
            // Очищаем URL от полного домена, оставляем только относительный путь
            $imageUrl = $request->image_url;
            if ($imageUrl && (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'))) {
                $parsedUrl = parse_url($imageUrl);
                $imageUrl = $parsedUrl['path'] ?? $imageUrl;
            }
            $news->image_url = $imageUrl;
        }
        $news->is_important = $request->has('is_important');
        $news->post_date = $request->post_date;
        
        $news->save();
        
        \Log::info('News updated successfully', [
            'news_id' => $news->id,
            'title' => $news->title,
            'content_length' => strlen($news->content)
        ]);

            return redirect()->route('admin.news.index')
                ->with('success', __('admin_news.news_updated_successfully'));
        } catch (\Exception $e) {
            \Log::error('Error updating news', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'news_id' => $news->id
            ]);
            
            return back()->withInput()->withErrors(['error' => 'Произошла ошибка при обновлении новости: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        $news->delete();
        
        return redirect()->route('admin.news.index')
            ->with('success', __('admin_news.news_deleted_successfully'));
    }
}
