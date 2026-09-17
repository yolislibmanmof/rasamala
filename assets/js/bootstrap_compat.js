/**
 * Bootstrap 4 to 5 Data Attributes Compatibility Shim
 * Automatically maps legacy Bootstrap 4 data attributes to Bootstrap 5 data-bs-* attributes
 * Supports dynamically loaded elements using MutationObserver
 */
(function () {
    'use strict';

    const mappings = {
        'data-toggle': 'data-bs-toggle',
        'data-target': 'data-bs-target',
        'data-dismiss': 'data-bs-dismiss',
        'data-slide': 'data-bs-slide'
    };

    function mapAttrs(el) {
        if (!el || el.nodeType !== 1) return;
        for (const [bs4, bs5] of Object.entries(mappings)) {
            if (el.hasAttribute(bs4) && !el.hasAttribute(bs5)) {
                el.setAttribute(bs5, el.getAttribute(bs4));
            }
        }
    }

    function processNodes(node) {
        if (!node) return;
        if (node.nodeType === 1) {
            mapAttrs(node);
            const selector = Object.keys(mappings).join(', ');
            node.querySelectorAll(selector).forEach(mapAttrs);
        }
    }

    // Run mapping as soon as script runs if body is ready
    if (document.body) {
        processNodes(document.body);
    }

    // Run again on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function () {
        processNodes(document.body);

        // Observe DOM changes for dynamically loaded elements
        if (window.MutationObserver) {
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.addedNodes) {
                        mutation.addedNodes.forEach(processNodes);
                    }
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    });
})();
