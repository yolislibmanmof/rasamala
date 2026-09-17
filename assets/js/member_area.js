/*!
 * Rasamala theme - member area interactions.
 */
'use strict';

(function () {
    const defaultConfig = {
        reserveDirectDatabase: false,
        urls: {
            basket: 'index.php?p=member&sec=title_basket',
            member: 'index.php?p=member',
            bookmark: 'index.php?p=member&sec=bookmark'
        },
        labels: {}
    };

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

    const config = Object.assign({}, defaultConfig, readJsonTemplate('rasamala-member-area-config', {}));
    config.urls = Object.assign({}, defaultConfig.urls, config.urls || {});
    config.labels = Object.assign({}, defaultConfig.labels, config.labels || {});

    const label = (key, fallback) => config.labels[key] || fallback;

    const escapeHtml = (value) => String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const notify = (type, message, options = {}) => {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            if (Object.keys(options).length > 0) {
                window.toastr[type](message, '', options);
                return;
            }
            window.toastr[type](message);
        }
    };

    const clearNotifications = () => {
        if (window.toastr && typeof window.toastr.clear === 'function') {
            window.toastr.clear();
        }
    };

    const showConfirmModal = (title, message, onConfirm) => {
        let modalEl = document.getElementById('confirm-action-modal');
        if (!modalEl) {
            modalEl = document.createElement('div');
            modalEl.id = 'confirm-action-modal';
            modalEl.className = 'modal fade';
            modalEl.setAttribute('tabindex', '-1');
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.innerHTML = [
                '<div class="modal-dialog modal-dialog-centered">',
                '<div class="modal-content">',
                '<div class="modal-header">',
                '<h5 class="modal-title" id="confirmModalLabel"></h5>',
                '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label=""></button>',
                '</div>',
                '<div class="modal-body"><p id="confirmModalMessage"></p></div>',
                '<div class="modal-footer">',
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-confirm-cancel></button>',
                '<button type="button" class="btn btn-danger" id="confirmModalYes"></button>',
                '</div>',
                '</div>',
                '</div>'
            ].join('');
            document.body.appendChild(modalEl);
        }

        modalEl.querySelector('#confirmModalLabel').textContent = title;
        modalEl.querySelector('#confirmModalMessage').textContent = message;
        modalEl.querySelector('[data-confirm-cancel]').textContent = label('cancel', 'Cancel');
        modalEl.querySelector('#confirmModalYes').textContent = label('confirm', 'Confirm');
        modalEl.querySelector('.btn-close').setAttribute('aria-label', label('close', 'Close'));

        const yesBtn = modalEl.querySelector('#confirmModalYes');
        const newYesBtn = yesBtn.cloneNode(true);
        yesBtn.parentNode.replaceChild(newYesBtn, yesBtn);

        const bsModal = window.bootstrap && window.bootstrap.Modal
            ? new window.bootstrap.Modal(modalEl)
            : null;

        newYesBtn.addEventListener('click', () => {
            if (bsModal) bsModal.hide();
            onConfirm();
        });

        if (bsModal) {
            bsModal.show();
            return;
        }

        if (window.confirm(`${title}\n\n${message}`)) {
            onConfirm();
        }
    };

    const parseAjaxResponse = (response) => {
        if (typeof response !== 'string') return response;

        try {
            return JSON.parse(response);
        } catch (error) {
            return {message: response};
        }
    };

    const redirectLater = (url, delay = 1000) => {
        window.setTimeout(() => {
            window.location.href = url;
        }, delay);
    };

    const initMemberActions = () => {
        if (!window.jQuery) return;

        const $ = window.jQuery;

        $('.memberDetail, .memberLoanList, .memberBookmarkList, .memberBasketList').each(function () {
            const table = $(this);
            if (!table.parent().hasClass('table-responsive')) {
                table.wrap('<div class="table-responsive"></div>');
            }
        });

        $('.clearAll').off('click.rasamalaMember').on('click.rasamalaMember', function (evt) {
            evt.preventDefault();
            const anchor = $(this);
            const url = anchor.attr('href');
            const postData = anchor.attr('postdata');

            showConfirmModal(label('clearBasketTitle', 'Clear Basket'), label('clearBasketMessage', 'Clear your title(s) basket?'), () => {
                notify('info', label('clearingBasket', 'Clearing basket...'), {timeOut: 0, extendedTimeOut: 0});
                $.ajax({
                    type: 'POST',
                    url,
                    cache: false,
                    data: postData,
                    async: true,
                    success: () => {
                        clearNotifications();
                        notify('success', label('basketCleared', 'Basket data cleared!'));
                        redirectLater(url);
                    },
                    error: () => {
                        clearNotifications();
                        notify('error', label('basketClearFailed', 'Failed to clear basket.'));
                    }
                });
            });
        });

        $('.clearOne').off('click.rasamalaMember').on('click.rasamalaMember', function (evt) {
            evt.preventDefault();
            const basketForm = $('#memberBasketListForm');
            const basketData = `${basketForm.serialize()}&basketRemove=1`;
            const basketAction = basketForm.attr('action');

            showConfirmModal(label('removeBasketTitle', 'Remove from Basket'), label('removeBasketMessage', 'Remove selected title(s) from basket?'), () => {
                notify('info', label('removingBasket', 'Removing selected titles...'), {timeOut: 0, extendedTimeOut: 0});
                $.ajax({
                    type: 'POST',
                    url: basketAction,
                    cache: false,
                    data: basketData,
                    async: true,
                    success: () => {
                        clearNotifications();
                        notify('success', label('basketRemoved', 'Selected basket data removed!'));
                        redirectLater(config.urls.basket);
                    },
                    error: () => {
                        clearNotifications();
                        notify('error', label('basketRemoveFailed', 'Failed to remove selected titles.'));
                    }
                });
            });
        });

        $('.reserve').off('click.rasamalaMember').on('click.rasamalaMember', function (evt) {
            evt.preventDefault();
            const anchor = $(this);
            const url = anchor.attr('href');
            $('#info').html(`<div class="alert alert-info">${label('reservationInfo', 'Please wait. your reservation is being sent')}...</div>`);
            notify('info', label('reservationSending', 'Please wait, your reservation is being sent...'), {timeOut: 0, extendedTimeOut: 0});

            $.ajax({
                type: 'POST',
                url,
                cache: false,
                data: 'sendReserve=1',
                async: true,
                success: (ajaxRespond) => {
                    clearNotifications();
                    const payload = parseAjaxResponse(ajaxRespond);

                    if (!config.reserveDirectDatabase) {
                        notify('success', label('reservationEmailSent', 'Reservation e-mail sent'));
                        redirectLater(url, 3000);
                        return;
                    }

                    const rows = Array.isArray(payload) ? payload : [payload];
                    rows.forEach((row) => {
                        const message = row && row.message ? row.message : label('reservationSent', 'Reservation request sent');
                        if (row && row.status === 'ERROR') {
                            notify('error', message);
                            return;
                        }

                        notify('success', message);
                        redirectLater(config.urls.basket, 2500);
                    });
                },
                error: () => {
                    clearNotifications();
                    notify('error', label('unexpectedError', 'Unexpected error occurred.'));
                }
            });
        });

        $('.deleteBookmark').off('click.rasamalaMember').on('click.rasamalaMember', function (evt) {
            evt.preventDefault();
            const id = $(this).data('id');

            showConfirmModal(label('removeBookmarkTitle', 'Remove Bookmark'), label('removeBookmarkMessage', 'Remove this bookmark?'), () => {
                notify('info', label('removingBookmark', 'Removing bookmark...'), {timeOut: 0, extendedTimeOut: 0});
                $.ajax({
                    type: 'POST',
                    url: config.urls.member,
                    data: {bookmark_id: id, delete_bookmark: true},
                    dataType: 'json',
                    async: true,
                    success: (res) => {
                        clearNotifications();
                        if (!res.status) {
                            notify('error', res.message);
                            return;
                        }

                        notify('success', res.message, {
                            timeOut: 2000,
                            onHidden: () => window.location.replace(config.urls.bookmark)
                        });
                    },
                    error: () => {
                        clearNotifications();
                        notify('error', label('bookmarkRemoveFailed', 'Unexcpected error. Please tell it to the librarian'));
                    }
                });
            });
        });
    };

    const fullscreenElement = () => document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;

    const initDigitalCard = () => {
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        const minimizeBtn = document.getElementById('minimize-btn');
        const printBtn = document.getElementById('print-btn');
        const cardContainer = document.getElementById('card-container');
        if (!fullscreenBtn || !minimizeBtn || !printBtn || !cardContainer) return;

        const syncFullscreenState = () => {
            const isFullscreen = !!fullscreenElement();
            cardContainer.classList.toggle('is-fullscreen', isFullscreen);
            fullscreenBtn.hidden = isFullscreen;
            minimizeBtn.hidden = !isFullscreen;
        };

        fullscreenBtn.addEventListener('click', () => {
            const request = cardContainer.requestFullscreen || cardContainer.webkitRequestFullscreen || cardContainer.msRequestFullscreen;
            if (request) request.call(cardContainer);
        });

        minimizeBtn.addEventListener('click', () => {
            const exit = document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen;
            if (exit) exit.call(document);
        });

        document.addEventListener('fullscreenchange', syncFullscreenState);
        document.addEventListener('webkitfullscreenchange', syncFullscreenState);
        document.addEventListener('msfullscreenchange', syncFullscreenState);
        syncFullscreenState();

        printBtn.addEventListener('click', () => {
            const printWindow = window.open('', '_blank');
            if (!printWindow) return;

            printWindow.document.write('<html><head><title>');
            printWindow.document.write(escapeHtml(label('printMemberCard', 'Print Member Card')));
            printWindow.document.write('</title>');
            printWindow.document.write('<style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#fff}#card-container{border:1px solid #ddd;box-shadow:none}</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(cardContainer.outerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            window.setTimeout(() => {
                printWindow.print();
                window.setTimeout(() => printWindow.close(), 500);
            }, 250);
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        initMemberActions();
        initDigitalCard();
    });
}());
