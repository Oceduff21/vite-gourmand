/**
 * FRONT — scroll-jump.js
 * Boutons navigation rapide haut / bas de page.
 */
(function () {
    'use strict';

    function initScrollJump() {
        var toTop = document.getElementById('scroll-to-top');
        var toBottom = document.getElementById('scroll-to-bottom');
        if (!toTop || !toBottom) return;

        var edgeThreshold = 80;
        var scrollUpThreshold = 280;
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function getScrollY() {
            return window.pageYOffset || document.documentElement.scrollTop || (document.body ? document.body.scrollTop : 0) || 0;
        }

        function getScrollMax() {
            var docHeight = Math.max(document.documentElement.scrollHeight, document.body ? document.body.scrollHeight : 0);
            return Math.max(0, docHeight - window.innerHeight);
        }

        function isScrollable() {
            return getScrollMax() > edgeThreshold;
        }

        function updateScrollButtons() {
            if (!isScrollable()) {
                toTop.hidden = true;
                toBottom.hidden = true;
                return;
            }
            var y = getScrollY();
            var maxY = getScrollMax();
            var nearTop = y <= edgeThreshold;
            var nearBottom = y >= maxY - edgeThreshold;
            var scrolledDown = y > scrollUpThreshold;
            toBottom.hidden = !nearTop;
            toTop.hidden = !(nearBottom || scrolledDown);
        }

        function scrollToPosition(top) {
            window.scrollTo({ top: Math.max(0, top), behavior: reduceMotion ? 'auto' : 'smooth' });
        }

        toTop.addEventListener('click', function () { scrollToPosition(0); });
        toBottom.addEventListener('click', function () {
            var pageEnd = document.getElementById('page-end');
            if (pageEnd) {
                pageEnd.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'end' });
            } else {
                scrollToPosition(getScrollMax());
            }
        });

        window.addEventListener('scroll', updateScrollButtons, { passive: true });
        window.addEventListener('resize', updateScrollButtons);
        window.addEventListener('load', updateScrollButtons);
        updateScrollButtons();
        setTimeout(updateScrollButtons, 300);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScrollJump);
    } else {
        initScrollJump();
    }
})();
