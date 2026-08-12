@if (session('flash_notice') && is_array(session('flash_notice')))
    @php
        $message = session('flash_notice')['message'] ?? '';
        $type = session('flash_notice')['type'] ?? 'info';
    @endphp
    <script>
        (function () {
            var shown = false;
            var run = function () {
                if (shown) {
                    return true;
                }
                if (typeof showToast === 'function') {
                    shown = true;
                    showToast(@json($message), @json($type));
                    return true;
                }
                return false;
            };
            if (!run()) {
                document.addEventListener('DOMContentLoaded', run);
                setTimeout(run, 300);
            }
        })();
    </script>
@endif

@if (session('flash_modal') && is_array(session('flash_modal')))
    @php
        $modalMessage = session('flash_modal')['message'] ?? '';
        $modalType = session('flash_modal')['type'] ?? 'success';
        $modalTitle = session('flash_modal')['title'] ?? '';
    @endphp
    <script>
        (function () {
            var shown = false;
            var run = function () {
                if (shown) {
                    return true;
                }
                if (typeof showModal === 'function') {
                    shown = true;
                    showModal(@json($modalMessage), @json($modalType), @json($modalTitle));
                    return true;
                }
                return false;
            };
            if (!run()) {
                document.addEventListener('DOMContentLoaded', run);
                setTimeout(run, 300);
            }
        })();
    </script>
@endif