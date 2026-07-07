@extends('layouts.site')

@section('title', $page_title . ' | ' . (\App\Models\SiteSetting::current()->seo_title ?? 'FULL MARKS ACADEMY'))
@section('meta_description', $page_description)

@push('styles')
<style>
  .detail-hero {
    position: relative;
    padding: 160px 0 100px 0;
    background-size: cover;
    background-position: center;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .detail-hero-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: radial-gradient(circle at center, rgba(5,4,2,0.4) 0%, rgba(5,4,2,0.95) 100%);
    z-index: 1;
  }
  .theme-light .detail-hero-overlay { background: radial-gradient(circle at center, rgba(255,255,255,0.4) 0%, rgba(248,250,252,0.95) 100%); }
  .theme-dark .detail-hero-overlay { background: radial-gradient(circle at center, rgba(5,7,15,0.4) 0%, rgba(5,7,15,0.95) 100%); }
  .detail-hero-content { position: relative; z-index: 2; }
  
  .news-content-wrapper {
    background: var(--bg-primary);
    padding: 60px 0;
  }
  .news-body {
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--text-secondary);
  }
  .news-body img {
    max-width: 100%;
    border-radius: var(--radius-lg);
    margin: 20px 0;
  }

  .similar-news-card {
    display: flex;
    flex-direction: column;
    height: 100%;
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: transform var(--transition-normal), box-shadow var(--transition-normal);
    border: 1px solid var(--glass-border);
    background: var(--bg-secondary);
  }
  .similar-news-card:hover { 
      transform: translateY(-5px); 
      box-shadow: var(--shadow-lg); 
      border-color: var(--accent-color);
  }
  .similar-news-img {
    height: 200px;
    object-fit: cover;
    width: 100%;
  }
  .similar-news-body {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
  }
</style>
@endpush

@section('content')
  <!-- Hero Section -->
  @php
    $imagePath = $news->image ? (\Illuminate\Support\Str::startsWith($news->image, ['http', 'site/']) ? asset($news->image) : asset('storage/' . $news->image)) : asset('site/images/placeholder.jpg');
  @endphp
  <section class="detail-hero" style="background-image: url('{{ $imagePath }}');">
    <div class="detail-hero-overlay"></div>
    <div class="container px-4 text-center detail-hero-content">
      <div class="reveal-scale">
        <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-4 uppercase" style="color: var(--text-primary);">
            {{ $news->translation ? $news->translation->title : '' }}
        </h1>
        <div class="mx-auto w-16 h-1 bg-gold rounded-full mb-4"></div>
        <div style="color: var(--text-muted); font-size: 0.9rem;">
            <i class="bi bi-calendar3"></i> {{ $news->created_at->format('Y-m-d') }}
        </div>
      </div>
    </div>
  </section>

  <!-- News Content Section -->
  <section class="news-content-wrapper">
    <div class="container px-4">
      <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="glass-panel p-5 reveal">
                <div class="news-body">
                    {!! $news->translation ? $news->translation->description : '' !!}
                </div>
            </div>
        </div>
      </div>

      <!-- Similar News Section -->
      @if($similarNews->isNotEmpty())
      <div class="mt-16 reveal">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <h5 class="section-subtitle">{{ app()->getLocale() == 'ar' ? 'أخبار أخرى' : 'Other News' }}</h5>
            <h2 class="section-title">{{ app()->getLocale() == 'ar' ? 'الأخبار المشابهة' : 'Similar News' }}</h2>
            <div class="section-divider mx-auto"></div>
        </div>
        
        <div class="row g-4 justify-content-center">
            @foreach($similarNews as $similar)
                @php
                    $similarImage = $similar->image ? (\Illuminate\Support\Str::startsWith($similar->image, ['http', 'site/']) ? asset($similar->image) : asset('storage/' . $similar->image)) : asset('site/images/placeholder.jpg');
                    $encryptedSimilarId = \Illuminate\Support\Facades\Crypt::encrypt($similar->id);
                @endphp
                <div class="col-md-4">
                    <a href="{{ route('site.news.show', $encryptedSimilarId) }}" class="text-decoration-none">
                        <div class="similar-news-card glass-panel">
                            <img src="{{ $similarImage }}" class="similar-news-img" alt="{{ $similar->translation->title ?? '' }}">
                            <div class="similar-news-body">
                                <h3 class="text-xl font-bold mb-3" style="color: var(--text-primary);">
                                    {{ \Illuminate\Support\Str::limit($similar->translation->title ?? '', 50) }}
                                </h3>
                                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($similar->translation->description ?? ''), 100) }}
                                </p>
                                <div class="mt-auto text-end" style="color: var(--accent-color); font-weight: 600;">
                                    {{ app()->getLocale() == 'ar' ? 'اقرأ المزيد' : 'Read More' }} &rarr;
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
      </div>
      @endif
    </div>
  </section>
@endsection
