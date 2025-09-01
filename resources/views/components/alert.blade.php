<div class="px-4 pt-4">
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <p class="text-white mb-0">{{ session()->get('success') }}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <p class="text-dark mb-0">{{ session()->get('warning') }}</p>
            @if (session()->has('error_details'))
                <details class="mt-2">
                    <summary class="text-dark" style="cursor: pointer;">View Error Details</summary>
                    <pre class="mt-2 text-dark small">{{ session()->get('error_details') }}</pre>
                </details>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <p class="text-white mb-0">{{ session()->get('error') }}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>
