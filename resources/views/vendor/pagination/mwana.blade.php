@if ($paginator->hasPages())
    <nav class="mwana-pagination" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="mwana-page-link disabled" aria-disabled="true">&larr; Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="mwana-page-link">&larr; Previous</a>
        @endif

        <span class="mwana-page-info">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="mwana-page-link">Next &rarr;</a>
        @else
            <span class="mwana-page-link disabled" aria-disabled="true">Next &rarr;</span>
        @endif
    </nav>
@endif
