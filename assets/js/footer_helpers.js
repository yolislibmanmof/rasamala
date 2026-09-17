/*!
 * Rasamala theme - footer helpers.
 */
'use strict';

(function () {
    const readJsonTemplate = (id, fallback = null) => {
        const element = document.getElementById(id);
        if (!element) return fallback;

        const text = (element.content ? element.content.textContent : element.textContent) || '';
        if (!text.trim()) return fallback;

        try {
            return JSON.parse(text);
        } catch (error) {
            return fallback;
        }
    };

    const stripModalInert = () => {
        document.querySelectorAll('.modal[inert]').forEach((modal) => {
            modal.removeAttribute('inert');
        });
    };

    const applySearchHighlight = () => {
        const keywords = readJsonTemplate('rasamala-highlight-keywords', null);
        if (!keywords || !window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.highlight !== 'function') {
            return;
        }

        window.jQuery('.card-body > *').highlight(keywords);
    };

    // Ensure mobile floating quick actions pill is attached at document.body level so it is free from parent stacking context clipping
    const promoteFloatingPillToBody = () => {
        const floatingPill = document.querySelector('.detail-floating-quick-actions');
        if (floatingPill && floatingPill.parentElement && floatingPill.parentElement !== document.body) {
            document.body.appendChild(floatingPill);
        }
    };

    if (document.readyState !== 'loading') {
        stripModalInert();
        applySearchHighlight();
        promoteFloatingPillToBody();
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            stripModalInert();
            applySearchHighlight();
            promoteFloatingPillToBody();
        });
    }

    if (window.jQuery) {
        window.jQuery(document).on('show.bs.modal hidden.bs.modal', '.modal', (event) => {
            event.target.removeAttribute('inert');
        });

        window.jQuery(document).on('click', '[data-bs-target="#adv-modal"], [data-target="#adv-modal"], .open-adv-modal-btn', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const modalEl = document.getElementById('adv-modal');
            if (!modalEl) return;
            if (modalEl.parentElement && modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            document.body.classList.add('modal-open');
            if (window.bootstrap && window.bootstrap.Modal) {
                try {
                    const modal = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                    if (modal) { modal.show(); return; }
                } catch (err) {}
            }
            if (window.jQuery.fn && window.jQuery.fn.modal) {
                window.jQuery(modalEl).modal('show');
            }
        });

        window.jQuery(document).on('click', '[data-bs-target="#whatsappModal"], [data-target="#whatsappModal"], #floating-whatsapp-btn', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const modalEl = document.getElementById('whatsappModal');
            if (!modalEl) return;
            if (modalEl.parentElement && modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            document.body.classList.add('modal-open');
            if (window.bootstrap && window.bootstrap.Modal) {
                try {
                    const modal = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                    if (modal) { modal.show(); return; }
                } catch (err) {}
            }
            if (window.jQuery.fn && window.jQuery.fn.modal) {
                window.jQuery(modalEl).modal('show');
            }
        });
    }
}());
