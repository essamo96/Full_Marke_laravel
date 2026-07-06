@props([
    'title',
    'icon' => null,
    'url' => '#',
    'active' => false,
    'textColor' => '',
    'iconSize' => 'fs-2',
    'textSize' => ''
])

<div class="menu-item">
    <a class="menu-link {{ $active ? 'active' : '' }}" href="{{ $url }}">
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
    </a>
</div>
