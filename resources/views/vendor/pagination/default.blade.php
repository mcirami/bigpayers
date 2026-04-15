@if ($paginator->hasPages())
    @php
        $d_from = request()->query('d_from');
		$d_to = request()->query('d_to');
		$dateSelect = request()->query('dateSelect');
    @endphp

        <ul class="pagination-container">
           {{-- --}}{{-- Previous Page Link --}}{{--
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}&d_from={{$d_from}}&d_to={{$d_to}}&dateSelect={{$dateSelect}}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                </li>
            @endif--}}

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active" aria-current="page"><a class="disabled active value_span2-2 value_span3-2 value_span6-1 value_span2 value_span6 value_span4">{{ $page }}</a></li>
                        @else
                            <li><a class="value_span2-2 value_span3-2 value_span6-1 value_span2 value_span6" href="{{ $url }}&d_from={{$d_from}}&d_to={{$d_to}}&dateSelect={{$dateSelect}}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            {{--@if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}&d_from={{$d_from}}&d_to={{$d_to}}&dateSelect={{$dateSelect}}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
            @else
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span aria-hidden="true">&rsaquo;</span>
                </li>
            @endif--}}
        </ul>

@endif
