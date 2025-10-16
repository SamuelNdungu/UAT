@php
    // Ensure $buttons is an array of descriptors:
    // ['url'=>string, 'label'=>string, 'icon'=>'fas fa-icon', 'variant'=>'primary|warning|success|secondary|outline-warning', 'target'=>'_blank' (optional), 'attrs'=>['aria-label'=>'...','title'=>'...']]
    $buttons = $buttons ?? [];
@endphp

<style>
    /* scoped styles for shared action buttons */
    .shared-action-group { display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center; margin:0; padding:0; }
    .shared-action-btn { min-width:140px; display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; padding:.35rem .6rem; font-size:0.9rem; text-decoration:none; }
    @media (max-width:575px){ .shared-action-btn{ min-width:100%; } }
</style>

<div class="shared-action-group">
    @foreach($buttons as $btn)
        @php
            $url = $btn['url'] ?? '#';
            $label = $btn['label'] ?? '';
            $icon = $btn['icon'] ?? '';
            $variant = $btn['variant'] ?? 'secondary';
            $target = $btn['target'] ?? null;
            $attrs = $btn['attrs'] ?? [];
            // build extra attributes string safely
            $extra = '';
            foreach($attrs as $k => $v) {
                $extra .= ' ' . e($k) . '="' . e($v) . '"';
            }
            // normalize variant into bootstrap class (supports 'outline-*' as provided)
            $btnClass = 'btn btn-sm shared-action-btn btn-' . $variant;
        @endphp

        <a href="{{ $url }}" class="{{ $btnClass }}" {!! $extra !!} @if($target) target="{{ $target }}" rel="noopener" @endif>
            @if($icon) <i class="{{ $icon }}" aria-hidden="true"></i> @endif
            <span>{{ $label }}</span>
        </a>
    @endforeach
</div>
