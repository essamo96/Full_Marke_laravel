@props([
    'title',
    'icon' => null,
    'active' => false,
    'textColor' => '',
    'iconSize' => 'fs-2',
    'textSize' => ''
])

<div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $active ? 'here show' : '' }}">
    <a href="javascript:void(0)" class="menu-link">
        @if($icon)
            <span class="menu-icon">
                <i class="{{ $icon }} {{ $iconSize }}" style="{{ $textColor ? 'color: ' . $textColor . ';' : '' }}"></i>
            </span>
        @else
            <span class="menu-bullet">
                <span class="bullet bullet-dot"></span>
            </span>
        @endif
        <span class="menu-title {{ $textSize }}" style="{{ $textColor ? 'color: ' . $textColor . ';' : '' }}">{{ $title }}</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="menu-sub menu-sub-accordion">
        {{ $slot }}
    </div>
</div>
