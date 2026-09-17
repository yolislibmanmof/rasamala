(function () {
    'use strict';

    const initializeDetailPage = function () {
        const detailRecord = document.querySelector('.detail-record');
        const closeLabel = detailRecord ? detailRecord.getAttribute('data-lightbox-close-label') : '';
        const previewLabel = detailRecord ? detailRecord.getAttribute('data-lightbox-preview-label') : '';
        const progressBar = document.getElementById('reading-progress-bar');

        if (progressBar) {
            window.addEventListener('scroll', function () {
                const scrollTop = document.body.scrollTop || document.documentElement.scrollTop;
                const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
                progressBar.style.width = scrolled + '%';
            }, { passive: true });
        }

        const adjustStickyBarPosition = function () {
            const stickyBar = document.getElementById('detail-floating-quick-actions');
            if (!stickyBar) return;
            const ticker = document.querySelector('.latest-content-ticker');
            stickyBar.classList.toggle(
                'has-ticker-above',
                Boolean(ticker && window.getComputedStyle(ticker).display !== 'none')
            );
        };
        adjustStickyBarPosition();

        const availabilityMoreButton = document.querySelector('[data-detail-avail-more]');
        if (availabilityMoreButton) {
            availabilityMoreButton.addEventListener('click', function () {
                document.querySelectorAll('[data-avail-hidden="1"]').forEach(function (row) {
                    row.classList.remove('detail-avail-row-hidden');
                    row.removeAttribute('data-avail-hidden');
                });
                availabilityMoreButton.style.display = 'none';
            });
        }

        document.addEventListener('click', function (event) {
            const clickedElement = event.target instanceof Element ? event.target : null;
            const clickedRow = clickedElement ? clickedElement.closest('.detail-avail-row') : null;
            document.querySelectorAll('.detail-avail-popover.show').forEach(function (popover) {
                if (!clickedRow || !clickedRow.contains(popover)) {
                    popover.classList.remove('show');
                }
            });

            if (!clickedRow) return;
            const popover = clickedRow.querySelector('.detail-avail-popover');
            if (popover) popover.classList.toggle('show');
        });

        document.addEventListener('click', function (event) {
            const clickedElement = event.target instanceof Element ? event.target : null;
            const target = clickedElement
                ? clickedElement.closest('a.colorbox, a[data-lightbox], .cover-preview-link')
                : null;
            if (!target) return;

            const href = target.getAttribute('href');
            if (!href) return;

            let imageUrl;
            try {
                imageUrl = new URL(href, window.location.href);
            } catch (error) {
                return;
            }
            if (!['http:', 'https:'].includes(imageUrl.protocol)
                || !/\.(jpe?g|png|gif|webp|svg)$/i.test(imageUrl.pathname)) {
                return;
            }

            event.preventDefault();
            const existingModal = document.getElementById('native-lightbox-modal');
            if (existingModal) existingModal.remove();

            const modal = document.createElement('div');
            modal.className = 'modal fade show native-lightbox-modal';
            modal.id = 'native-lightbox-modal';
            modal.tabIndex = -1;
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');

            const dialog = document.createElement('div');
            dialog.className = 'modal-dialog modal-dialog-centered modal-lg';
            const content = document.createElement('div');
            content.className = 'modal-content bg-transparent border-0 text-end';
            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'btn-close btn-close-white ms-auto mb-2 native-lightbox-close';
            closeButton.setAttribute('aria-label', closeLabel);
            const image = document.createElement('img');
            image.src = imageUrl.href;
            image.className = 'img-fluid rounded shadow-lg mx-auto d-block native-lightbox-preview';
            image.alt = previewLabel;
            image.decoding = 'async';

            content.appendChild(closeButton);
            content.appendChild(image);
            dialog.appendChild(content);
            modal.appendChild(dialog);
            document.body.appendChild(modal);

            const closeOnEscape = function (keyboardEvent) {
                if (keyboardEvent.key === 'Escape') removeLightbox();
            };
            const removeLightbox = function () {
                document.removeEventListener('keydown', closeOnEscape);
                modal.remove();
            };
            document.addEventListener('keydown', closeOnEscape);
            closeButton.addEventListener('click', removeLightbox);
            modal.addEventListener('click', function (modalEvent) {
                if (modalEvent.target === modal || modalEvent.target === dialog) {
                    removeLightbox();
                }
            });
            closeButton.focus();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeDetailPage, { once: true });
    } else {
        initializeDetailPage();
    }
}());
