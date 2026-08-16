{{-- Shared pagination footer for index tables --}}
@if (isset($items) && $items instanceof \Illuminate\Contracts\Pagination\Paginator && $items->hasPages())
    <div class="deras-pagination mt-4">
        {{ $items->onEachSide(1)->links('vendor.pagination.deras') }}
    </div>
@endif
