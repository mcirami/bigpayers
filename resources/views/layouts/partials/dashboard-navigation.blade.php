@php
    $mobile = $mobile ?? false;
@endphp

<div class="space-y-6">
    @foreach($menuSections as $section)
        <section class="space-y-3">
            <div class="bp-nav-section-title">
                @if(!empty($section['icon']))
                    <i class="{{ $section['icon'] }}" aria-hidden="true"></i>
                @endif
                <span>{{ $section['name'] }}</span>
            </div>

            <div class="space-y-2">
                @foreach($section['items'] as $item)
                    <a
                        href="{{ $item['url'] }}"
                        class="bp-nav-link {{ $item['active'] ? 'bp-nav-link-active' : '' }}"
                        @if($mobile) data-dashboard-nav-link @endif
                    >
                        <span>{{ $item['name'] }}</span>
                        <i class="fas fa-arrow-right text-[11px] opacity-50" aria-hidden="true"></i>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
