<div id="toast"
     class="hidden top-6 right-6 z-[9999] fixed w-full max-w-sm"
     role="alert">

    <div class="flex items-center bg-surface shadow-2xl p-4 border border-surface rounded-xl">

        {{-- Icon --}}
        <div id="toast-icon"
             class="inline-flex justify-center items-center rounded w-7 h-7 shrink-0">
        </div>

        {{-- Message --}}
        <div id="toast-message"
             class="ms-3 font-normal text-fg text-sm">
        </div>

        {{-- Close --}}
        <button id="toast-close"
            type="button"
            class="flex justify-center items-center ms-auto rounded w-8 h-8 text-fg-muted hover:text-fg transition">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>
</div>

{{-- ICON TEMPLATES --}}
<div class="hidden">
    <span id="icon-success">
        <span class="flex justify-center items-center bg-green-500/20 rounded-full w-7 h-7 text-accent-green">
            <x-heroicon-o-check class="w-4 h-4" />
        </span>
    </span>

    <span id="icon-error">
        <span class="flex justify-center items-center bg-red-500/20 rounded-full w-7 h-7 text-accent-red">
            <x-heroicon-o-x-mark class="w-4 h-4" />
        </span>
    </span>

    <span id="icon-warning">
        <span class="flex justify-center items-center bg-yellow-500/20 rounded-full w-7 h-7 text-accent-yellow">
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

