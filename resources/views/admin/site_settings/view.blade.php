@extends('layouts.admin')

@section('title', __('app.site_settings'))

@php($pageTitle = __('app.site_settings'))

@section('content')
    <form method="POST" action="{{ route('site_settings.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- ============ Hero Media (videos + fallback image) ============ --}}
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.hero_media') }}</h3>
            </div>
            <div class="card-body row g-5">
                @foreach (['hero_video_1', 'hero_video_2', 'hero_video_1_mobile', 'hero_video_2_mobile', 'about_video', 'about_video_mobile'] as $field)
                    <div class="col-md-6">
                        <label class="form-label">{{ __('app.'.$field) }}</label>
                        <input type="file" name="{{ $field }}" class="form-control" accept="video/mp4">
                        @if ($info->$field)
                            <div class="form-text">
                                {{ __('app.current_file') }}:
                                <a href="{{ asset($info->$field) }}" target="_blank">{{ basename($info->$field) }}</a>
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="col-md-6">
                    <label class="form-label">{{ __('app.hero_still_image') }}</label>
                    <input type="file" name="hero_still_image" class="form-control" accept="image/*">
                    @if ($info->hero_still_image)
                        <div class="form-text mb-2">{{ __('app.current_file') }}: {{ basename($info->hero_still_image) }}</div>
                        <img src="{{ asset($info->hero_still_image) }}" alt="" style="max-width:160px;border-radius:.5rem;">
                    @endif
                </div>
            </div>
        </div>

        {{-- ============ Social Media Links ============ --}}
        <div class="card mb-5">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0">{{ __('app.social_links') }}</h3>
                <button type="button" class="btn btn-sm btn-light-primary" id="addSocialRow">{{ __('app.add_row') }}</button>
            </div>
            <div class="card-body">
                <div id="socialLinksWrapper">
                    @foreach (($info->social_links ?: [['platform' => '', 'url' => '', 'icon' => null]]) as $i => $link)
                        <div class="row g-3 align-items-end mb-4 social-link-row">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('app.platform') }}</label>
                                <input type="text" name="social_links[{{ $i }}][platform]" value="{{ $link['platform'] ?? '' }}" class="form-control" placeholder="Facebook, Instagram...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.url') }}</label>
                                <input type="url" name="social_links[{{ $i }}][url]" value="{{ $link['url'] ?? '' }}" class="form-control" placeholder="https://...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('app.logo') }}</label>
                                <input type="file" name="social_links[{{ $i }}][icon]" class="form-control" accept="image/*">
                                @if (!empty($link['icon']))
                                    <img src="{{ asset($link['icon']) }}" alt="" style="height:28px;margin-top:.4rem;">
                                @endif
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-light-danger remove-social-row">{{ __('app.remove') }}</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============ SEO Settings ============ --}}
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.seo_settings') }}</h3>
            </div>
            <div class="card-body">
                <div class="mb-5">
                    <label class="form-label">{{ __('app.seo_title') }}</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $info->seo_title) }}" class="form-control">
                </div>
                <div class="mb-5">
                    <label class="form-label">{{ __('app.seo_description') }}</label>
                    <textarea name="seo_description" class="form-control" rows="3">{{ old('seo_description', $info->seo_description) }}</textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label">{{ __('app.seo_keywords') }}</label>
                    <textarea name="seo_keywords" class="form-control" rows="2">{{ old('seo_keywords', $info->seo_keywords) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ============ Maintenance / Closure Screen ============ --}}
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">{{ __('app.maintenance_settings') }}</h3>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-5">
                    <input type="checkbox" name="maintenance_mode" class="form-check-input" value="1" {{ old('maintenance_mode', $info->maintenance_mode) ? 'checked' : '' }}>
                    <label class="form-check-label">{{ __('app.maintenance_mode') }}</label>
                </div>
                <div class="mb-5">
                    <label class="form-label">{{ __('app.maintenance_title') }}</label>
                    <input type="text" name="maintenance_title" value="{{ old('maintenance_title', $info->maintenance_title) }}" class="form-control">
                </div>
                <div class="mb-0">
                    <label class="form-label">{{ __('app.maintenance_message') }}</label>
                    <textarea name="maintenance_message" class="form-control" rows="3">{{ old('maintenance_message', $info->maintenance_message) }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
    </form>
@endsection

@push('scripts')
<script>
  document.getElementById('addSocialRow').addEventListener('click', function () {
    const wrapper = document.getElementById('socialLinksWrapper');
    const index = wrapper.querySelectorAll('.social-link-row').length;
    const row = document.createElement('div');
    row.className = 'row g-3 align-items-end mb-4 social-link-row';
    row.innerHTML = `
      <div class="col-md-3">
        <label class="form-label">{{ __('app.platform') }}</label>
        <input type="text" name="social_links[${index}][platform]" class="form-control" placeholder="Facebook, Instagram...">
      </div>
      <div class="col-md-4">
        <label class="form-label">{{ __('app.url') }}</label>
        <input type="url" name="social_links[${index}][url]" class="form-control" placeholder="https://...">
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('app.logo') }}</label>
        <input type="file" name="social_links[${index}][icon]" class="form-control" accept="image/*">
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-light-danger remove-social-row">{{ __('app.remove') }}</button>
      </div>`;
    wrapper.appendChild(row);
  });

  document.getElementById('socialLinksWrapper').addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-social-row')) {
      e.target.closest('.social-link-row').remove();
    }
  });
</script>
@endpush
