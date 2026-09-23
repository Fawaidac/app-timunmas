{{--
    Live Search — auto-submit saat mengetik (debounce), tanpa perlu tekan Enter.
    Dipakai di layouts/app.blade.php & layouts/admin.blade.php.

    Cara pakai di halaman index:
        <form method="GET" action="{{ route('...') }}" class="search-form">
            <input type="search" name="search" value="{{ request('search') }}"
                   placeholder="Cari" class="search-input" autocomplete="off">
        </form>

    Input apa pun yang berada di dalam form dengan class `search-form` (atau memiliki
    atribut `data-live-search`) akan otomatis di-submit setelah pengguna berhenti mengetik.
--}}
<script>
    (function () {
        var DELAY = 400; // ms — jeda setelah berhenti mengetik

        function submitForm(form) {
            // Reset ke halaman 1 agar hasil pencarian tidak "tersangkut" di page lama
            var page = form.querySelector('input[name="page"]');
            if (page) {
                page.value = 1;
            } else {
                page = document.createElement('input');
                page.type = 'hidden';
                page.name = 'page';
                page.value = 1;
                form.appendChild(page);
            }
            form.submit();
        }

        function bind(input) {
            if (input.dataset.liveSearchBound === '1') {
                return;
            }
            input.dataset.liveSearchBound = '1';

            var timer = null;
            var form = input.form || input.closest('form');

            if (!form) {
                return;
            }

            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    submitForm(form);
                }, DELAY);
            });

            // Enter tetap berfungsi normal (submit langsung, tanpa jeda)
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    clearTimeout(timer);
                    e.preventDefault();
                    submitForm(form);
                }
            });
        }

        function bindSelect(sel) {
            if (sel.dataset.liveSearchBound === '1') return;
            sel.dataset.liveSearchBound = '1';
            var form = sel.form || sel.closest('form');
            if (!form) return;
            sel.addEventListener('change', function () {
                submitForm(form);
            });
        }

        function init(root) {
            var inputSelector = '.search-form input[type="search"], .search-form input[name="search"], [data-live-search]';
            var selectSelector = '.search-form select';

            if (root.matches && root.matches(inputSelector)) {
                bind(root);
            }
            if (root.matches && root.matches(selectSelector)) {
                bindSelect(root);
            }

            if (root.querySelectorAll) {
                Array.prototype.forEach.call(root.querySelectorAll(inputSelector), bind);
                Array.prototype.forEach.call(root.querySelectorAll(selectSelector), bindSelect);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                init(document);
            });
        } else {
            init(document);
        }
    })();
</script>
