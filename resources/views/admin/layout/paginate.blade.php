@if ($paginator->hasPages())
    <nav class="justify-content-center">
        <ul class="pagination justify-content-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item" disabled="">
                    <a class="page-link" disabled="" href="javascript:;"><i class="ci-arrow-right me-2"></i>قبلی</a>
                </li>
            @else

                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}"><i
                            class="ci-arrow-right me-2"></i>قبلی</a>
                </li>
            @endif


            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item" disabled="" aria-disabled="true"><span
                            class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())

                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}"><i
                            class="ci-arrow-right me-2"></i>  بعدی</a>
                </li>
            @else


                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}"><i
                            class="ci-arrow-right me-2"></i>  بعدی</a>
                </li>

            @endif
        </ul>
    </nav>
@endif
