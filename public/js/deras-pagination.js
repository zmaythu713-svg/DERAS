/**
 * DERAS pagination — subtle press feedback + loading state on navigate
 */
(function () {
    function bindPager(root) {
        if (!root || root.dataset.derasPagerBound === '1') return;
        root.dataset.derasPagerBound = '1';

        root.querySelectorAll('a.deras-pager__btn').forEach(function (link) {
            link.addEventListener('click', function () {
                var pager = link.closest('.deras-pager');
                if (pager) pager.classList.add('is-loading');
            });
        });
    }

    function init() {
        document.querySelectorAll('.deras-pagination .deras-pager').forEach(bindPager);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
