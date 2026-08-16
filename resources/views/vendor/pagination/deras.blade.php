@if ($paginator->hasPages())
    <nav class="deras-pager" role="navigation" aria-label="Pagination">
        <p class="deras-pager__meta">
            Showing
            <strong>{{ $paginator->firstItem() }}</strong>
            to
            <strong>{{ $paginator->lastItem() }}</strong>
            of
            <strong>{{ $paginator->total() }}</strong>
            results
        </p>

        <ul class="deras-pager__list">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="deras-pager__item is-disabled" aria-disabled="true">
                    <span class="deras-pager__btn" aria-hidden="true">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="deras-pager__item">
                    <a class="deras-pager__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="deras-pager__item is-ellipsis" aria-hidden="true">
                        <span class="deras-pager__btn">…</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="deras-pager__item is-active" aria-current="page">
                                <span class="deras-pager__btn">{{ $page }}</span>
                            </li>
                        @else
                            <li class="deras-pager__item">
                                <a class="deras-pager__btn" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="deras-pager__item">
                    <a class="deras-pager__btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="deras-pager__item is-disabled" aria-disabled="true">
                    <span class="deras-pager__btn" aria-hidden="true">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
