<div id="toast"
     class="fixed top-6 right-6 z-[9999] w-full max-w-sm hidden"
     role="alert">

    <div class="flex items-center p-4
                bg-white
                border border-neutral-200
                rounded-xl shadow-sm">

        {{-- Icon --}}
        <div id="toast-icon"
             class="inline-flex items-center justify-center shrink-0
                    w-7 h-7 rounded">
        </div>

        {{-- Message --}}
        <div id="toast-message"
             class="ms-3 text-sm font-normal text-neutral-700">
        </div>

        {{-- Close --}}
        <button id="toast-close"
            type="button"
            class="ms-auto flex items-center justify-center
                   text-neutral-400 hover:text-neutral-600
                   rounded h-8 w-8">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>
</div>

{{-- ICON TEMPLATES --}}
<div class="hidden">
    <span id="icon-success">
        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-green-100 text-green-600">
            <x-heroicon-o-check class="w-4 h-4" />
        </span>
    </span>

    <span id="icon-error">
        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-red-100 text-red-600">
            <x-heroicon-o-x-mark class="w-4 h-4" />
        </span>
    </span>

    <span id="icon-warning">
        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-orange-100 text-orange-600">
            <x-heroicon-o-exclamation-circle class="w-4 h-4" />
        </span>
    </span>
</div>


@once
    @push('scripts')
        <script>
            (function () {
                const toast = document.getElementById('toast');
                const messageEl = document.getElementById('toast-message');
                const iconEl = document.getElementById('toast-icon');
                const closeBtn = document.getElementById('toast-close');

                let timer = null;

                const icons = {
                    success: 'icon-success',
                    error: 'icon-error',
                    warning: 'icon-warning',
                };

                function showToast(message, type = 'success') {
                    if (!toast) return;

                    // message
                    messageEl.textContent = message;

                    // icon
                    iconEl.innerHTML = '';
                    iconEl.appendChild(
                        document.getElementById(icons[type] ?? icons.success)
                            .firstElementChild.cloneNode(true)
                    );

                    // show
                    toast.classList.remove('hidden', 'opacity-0', 'translate-x-2');

                    // reset timer
                    if (timer) clearTimeout(timer);

                    timer = setTimeout(hideToast, 3000);
                }

                function hideToast() {
                    toast.classList.add('opacity-0', 'translate-x-2');
                    setTimeout(() => toast.classList.add('hidden'), 200);
                }

                closeBtn.addEventListener('click', hideToast);

                // Livewire v3 browser event
                window.addEventListener('toast', (e) => {
                    showToast(e.detail.message, e.detail.type);
                });

                // expose for manual testing
                window.Toast = { show: showToast };
            })();
        </script>
    @endpush
@endonce

