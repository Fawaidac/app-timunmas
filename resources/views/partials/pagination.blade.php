@if(isset($paginator) && $paginator->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Menampilkan <b>{{ $paginator->firstItem() }}</b> - <b>{{ $paginator->lastItem() }}</b> dari total <b>{{ number_format($paginator->total(), 0, ',', '.') }}</b> {{ $itemLabel ?? 'data' }}
        </div>

        <ul class="pagination-container">
            {{-- First & Previous Button --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">«</span></li>
                <li class="page-item disabled"><span class="page-link">‹</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">«</a></li>
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a></li>
            @endif

            {{-- Smart Page Links with Windowing --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $onEachSide = 2; // Menampilkan 2 halaman di kiri & kanan halaman aktif

                $start = max(1, $currentPage - $onEachSide);
                $end = min($lastPage, $currentPage + $onEachSide);
            @endphp

            @if ($start > 1)
                <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                @if ($start > 2)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
            @endif

            @for ($i = $start; $i <= $end; $i++)
                @if ($i == $currentPage)
                    <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
                @endif
            @endfor

            @if ($end < $lastPage)
                @if ($end < $lastPage - 1)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
                <li class="page-item"><a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a></li>
            @endif

            {{-- Next & Last Button --}}
            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">›</a></li>
                <li class="page-item"><a class="page-link" href="{{ $paginator->url($lastPage) }}">»</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">›</span></li>
                <li class="page-item disabled"><span class="page-link">«</span></li>
            @endif
        </ul>
    </div>
@endif
