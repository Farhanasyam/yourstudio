<div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
    <div class="text-sm text-muted">
        Showing {{ $items->total() > 0 ? $items->firstItem() : 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} results
    </div>
    @if($items->hasPages())
        <div>{{ $items->appends(request()->query())->links() }}</div>
    @endif
</div>
