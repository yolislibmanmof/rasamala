/*!
 * Rasamala theme interactions.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const query = (selector, root = document) => root.querySelector(selector);
    const queryAll = (selector, root = document) => Array.prototype.slice.call(root.querySelectorAll(selector));

    // Citation links used to rely on SLiMS' Colorbox handler. Rasamala does
    // not load that legacy plugin, so the handler prevented the link's
    // default action and left the page progress bar running forever. Render
    // the citation in an in-page dialog instead of opening a browser tab or
    // a browser-dependent popup. The capture phase keeps the legacy handler
    // from intercepting the click.
    const citationDialog = {
        element: null,
        frame: null,
        loading: null,
        closeButton: null,
        previousFocus: null,
        timeoutId: null
    };

    const setCitationLoading = (state) => {
        const loading = citationDialog.loading;
        if (!loading) return;

        const icon = loading.querySelector('i');
        const text = loading.querySelector('span');
        const isError = state === 'error';

        loading.hidden = state === 'ready';
        loading.classList.toggle('is-error', isError);
        if (icon) {
            icon.className = isError ? 'fas fa-exclamation-triangle' : 'fas fa-spinner fa-spin';
        }
        if (text) {
            text.textContent = isError ? 'Citation could not be loaded.' : 'Loading citation...';
        }
    };

    const clearCitationTimeout = () => {
        if (citationDialog.timeoutId !== null) {
            window.clearTimeout(citationDialog.timeoutId);
            citationDialog.timeoutId = null;
        }
    };

    const closeCitationDialog = () => {
        if (!citationDialog.element) return;

        citationDialog.element.classList.remove('is-open');
        citationDialog.element.hidden = true;
        document.body.classList.remove('rasamala-citation-modal-open');
        clearCitationTimeout();
        setCitationLoading('ready');
        if (citationDialog.frame) citationDialog.frame.src = 'about:blank';

        if (citationDialog.previousFocus && document.contains(citationDialog.previousFocus)) {
            citationDialog.previousFocus.focus();
        }
    };

    const ensureCitationDialog = () => {
        if (citationDialog.element) return;

        const modal = document.createElement('div');
        modal.id = 'rasamala-citation-modal';
        modal.className = 'rasamala-citation-modal';
        modal.hidden = true;
        modal.innerHTML = `
            <div class="rasamala-citation-modal-backdrop" data-citation-close></div>
            <section class="rasamala-citation-dialog" role="dialog" aria-modal="true" aria-labelledby="rasamala-citation-title">
                <header class="rasamala-citation-dialog-header">
                    <div class="rasamala-citation-dialog-heading">
                        <span class="rasamala-citation-dialog-eyebrow">Citation</span>
                        <h2 id="rasamala-citation-title">Book citation</h2>
                    </div>
                    <button type="button" class="rasamala-citation-dialog-close" data-citation-close aria-label="Close citation">&times;</button>
                </header>
                <div class="rasamala-citation-frame-wrap">
                    <div class="rasamala-citation-loading" role="status">
                        <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                        <span>Loading citation...</span>
                    </div>
                    <iframe class="rasamala-citation-frame" title="Book citation" loading="eager"></iframe>
                </div>
                <footer class="rasamala-citation-dialog-footer">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-citation-close>Close</button>
                </footer>
            </section>`;

        document.body.appendChild(modal);
        citationDialog.element = modal;
        citationDialog.frame = modal.querySelector('.rasamala-citation-frame');
        citationDialog.loading = modal.querySelector('.rasamala-citation-loading');
        citationDialog.closeButton = modal.querySelector('.rasamala-citation-dialog-close');

        modal.addEventListener('click', (event) => {
            const closeTrigger = event.target && event.target.closest
                ? event.target.closest('[data-citation-close]')
                : null;
            if (closeTrigger) closeCitationDialog();
        });

        if (citationDialog.frame) {
            citationDialog.frame.addEventListener('load', () => {
                clearCitationTimeout();
                setCitationLoading('ready');
            });
            citationDialog.frame.addEventListener('error', () => {
                clearCitationTimeout();
                setCitationLoading('error');
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && citationDialog.element && citationDialog.element.classList.contains('is-open')) {
                event.preventDefault();
                closeCitationDialog();
            }
        });
    };

    const openCitationDialog = (link) => {
        ensureCitationDialog();
        if (!citationDialog.element || !citationDialog.frame) return;

        citationDialog.previousFocus = link;
        citationDialog.frame.title = link.getAttribute('data-title') || 'Book citation';
        clearCitationTimeout();
        setCitationLoading('loading');
        citationDialog.frame.src = link.href;
        citationDialog.timeoutId = window.setTimeout(() => {
            citationDialog.timeoutId = null;
            setCitationLoading('error');
        }, 10000);
        citationDialog.element.hidden = false;
        document.body.classList.add('rasamala-citation-modal-open');

        window.requestAnimationFrame(() => {
            citationDialog.element.classList.add('is-open');
            if (citationDialog.closeButton) citationDialog.closeButton.focus();
        });
    };

    document.addEventListener('click', (event) => {
        const citationLink = event.target && event.target.closest
            ? event.target.closest('a.citationLink')
            : null;

        if (!citationLink || !citationLink.href) return;

        event.preventDefault();
        event.stopImmediatePropagation();
        openCitationDialog(citationLink);
    }, true);

    const csrfToken = () => {
        const metaToken = query('meta[name="csrf-token"]');
        const inputToken = query('input[name="csrf_token"]');

        return (metaToken && metaToken.getAttribute('content')) || (inputToken && inputToken.value) || '';
    };

    const notify = (type, message, options = {}) => {
        const text = message || 'Request failed';

        if (window.toastr && typeof window.toastr[type] === 'function') {
            if (Object.keys(options).length > 0) {
                window.toastr[type](text, '', options);
                return;
            }

            window.toastr[type](text);
            return;
        }

        if (type === 'error') {
            console.error(text);
        }
    };

    const redirectWithToast = (message, url) => {
        const redirect = () => {
            window.location.replace(url);
        };

        if (window.toastr) {
            notify('error', message, {
                timeOut: 2000,
                onHidden: redirect
            });
            return;
        }

        console.error(message || 'Request failed');
        redirect();
    };

    const postForm = (url, data) => {
        const body = new URLSearchParams();

        Object.keys(data).forEach((key) => {
            const value = data[key];

            if (Array.isArray(value)) {
                value.forEach((item) => body.append(`${key}[]`, item));
                return;
            }

            body.append(key, value);
        });

        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body
        }).then((response) => response.text().then((text) => {
            let payload = {};

            if (text) {
                try {
                    payload = JSON.parse(text);
                } catch (error) {
                    payload = {message: text};
                }
            }

            if (!response.ok) {
                const requestError = new Error(payload.message || 'Request failed');
                requestError.payload = payload;
                throw requestError;
            }

            return payload;
        }));
    };

    const stripHtml = (value) => {
        const temporary = document.createElement('div');
        temporary.innerHTML = String(value || '');

        return temporary.textContent || temporary.innerText || '';
    };

    const copyInputValue = (input) => {
        if (!input) return Promise.resolve(false);

        const fallbackCopy = () => {
            input.focus();
            input.select();
            input.setSelectionRange(0, input.value.length);
            try {
                return document.execCommand('copy');
            } catch (error) {
                return false;
            }
        };

        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(input.value)
                .then(() => true)
                .catch(() => fallbackCopy());
        }

        return Promise.resolve(fallbackCopy());
    };

    const resizeFitHeightImages = () => {
        queryAll('.fit-height').forEach((image) => {
            image.style.height = '';
            const width = image.getBoundingClientRect().width || image.offsetWidth;

            if (width > 0) {
                image.style.height = `${(width * 83) / 65}px`;
            }
        });
    };

    resizeFitHeightImages();
    window.addEventListener('resize', resizeFitHeightImages);

    queryAll('#shareModalInput, #contentQrModalInput, .detail-qr-modal-input').forEach((input) => {
        input.addEventListener('focus', () => input.select());
        input.addEventListener('click', () => input.select());
    });

    queryAll('.mobile-language-select').forEach((select) => {
        select.addEventListener('change', () => {
            if (select.value) {
                const url = new URL(window.location.href);
                url.searchParams.set('select_lang', select.value);
                window.location.href = url.toString();
            }
        });
    });

    if (window.toastr) {
        window.toastr.options = {
            closeButton: false,
            debug: false,
            newestOnTop: false,
            progressBar: false,
            positionClass: 'toast-bottom-right',
            preventDuplicates: false,
            onclick: null,
            showDuration: '300',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };
    }

    queryAll('.add-to-chart-button').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();

            postForm('index.php?p=member', {
                biblio: [button.getAttribute('data-biblio') || ''],
                callback: 'json',
                csrf_token: csrfToken()
            })
                .then((data) => {
                    notify(data.status ? 'success' : 'error', data.message);

                    const newCount = typeof data.count !== 'undefined' ? data.count : 0;
                    queryAll('#count-basket, .count-basket, #count-basket-mobile, .basket-badge-mobile').forEach((basketCounter) => {
                        basketCounter.textContent = newCount;
                        basketCounter.classList.remove('basket-updated');
                        void basketCounter.offsetWidth;
                        basketCounter.classList.add('basket-updated');
                        setTimeout(() => basketCounter.classList.remove('basket-updated'), 500);
                    });
                })
                .catch((error) => {
                    console.error('ERROR!', error);
                    redirectWithToast((error.payload && error.payload.message) || error.message, 'index.php?p=member');
                });
        });
    });

    queryAll('.bookMarkBook').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();

            if (button.classList.contains('bg-success')) return;

            const id = button.getAttribute('data-id') || '';

            postForm('index.php?p=member&sec=bookmark', {
                bookmark_id: id,
                callback: 'json',
                csrf_token: csrfToken()
            })
                .then((res) => {
                    const successClasses = button.hasAttribute('data-detail')
                        ? ['bg-success', 'text-white', 'rounded-3', 'px-2', 'py-1']
                        : ['bg-success', 'text-white', 'rounded-3'];
                    const label = document.getElementById(`label-${id}`);

                    button.classList.remove('text-muted');
                    button.classList.add(...successClasses);

                    if (label) {
                        label.textContent = res.label || '';
                    }

                    notify('success', res.message);
                })
                .catch((error) => {
                    redirectWithToast(
                        (error.payload && error.payload.message) || error.message,
                        `index.php?p=member&destination=${encodeURIComponent(`${window.location.href}#card-${id}`)}`
                    );
                });
        });
    });

    document.addEventListener('click', (e) => {
        const shareBtn = e.target.closest('.detail-share-btn, .btn-theme-share, .btn-content-share, .btn-news-share');
        if (shareBtn) {
            e.preventDefault();
            e.stopPropagation();
            const url = shareBtn.getAttribute('data-url') || window.location.href;
            const title = shareBtn.getAttribute('data-title') || document.title;

            const openShareModal = () => {
                const modalTitle = document.getElementById('shareModalBookTitle');
                const modalInput = document.getElementById('shareModalInput');
                const waBtn = document.getElementById('shareWaBtn');
                const fbBtn = document.getElementById('shareFbBtn');
                const twBtn = document.getElementById('shareTwBtn');
                const tgBtn = document.getElementById('shareTelegramBtn');
                const liBtn = document.getElementById('shareLinkedinBtn');
                const emailBtn = document.getElementById('shareEmailBtn');

                if (modalTitle) modalTitle.textContent = title;
                if (modalInput) modalInput.value = url;
                if (waBtn) waBtn.href = `https://api.whatsapp.com/send?text=${encodeURIComponent(title + ' - ' + url)}`;
                if (fbBtn) fbBtn.href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                if (twBtn) twBtn.href = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;
                if (tgBtn) tgBtn.href = `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
                if (liBtn) liBtn.href = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
                if (emailBtn) emailBtn.href = `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(title + '\n' + url)}`;

                const modalEl = document.getElementById('mediaSocialModal');
                if (modalEl) {
                    forceShowModal(modalEl);
                } else if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(() => {
                        alert('Tautan berhasil disalin!');
                    });
                }
            };

            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            if (isMobile && navigator.share) {
                navigator.share({ title: title, text: title, url: url }).catch((err) => {
                    if (err && err.name !== 'AbortError') {
                        openShareModal();
                    }
                });
            } else {
                openShareModal();
            }
        }

        const copyBtn = e.target.closest('#shareCopyBtn');
        if (copyBtn) {
            e.preventDefault();
            const input = document.getElementById('shareModalInput');
            const alertBox = document.getElementById('shareCopySuccess');
            copyInputValue(input).then((copied) => {
                if (copied && alertBox) {
                    alertBox.classList.remove('d-none');
                    setTimeout(() => alertBox.classList.add('d-none'), 3500);
                } else if (!copied) {
                    notify('error', 'Unable to copy the link.');
                }
            });
        }

        const qrBtn = e.target.closest('.btn-news-qr, .btn-content-qr');
        if (qrBtn) {
            e.preventDefault();
            const url = qrBtn.getAttribute('data-url') || window.location.href;
            const title = qrBtn.getAttribute('data-title') || document.title;

            const qrImgWrap = document.getElementById('contentQrModalImage');
            const qrTitle = document.getElementById('contentQrModalTitle');
            const qrInput = document.getElementById('contentQrModalInput');
            const qrLink = document.getElementById('contentQrModalLink');

            if (qrImgWrap) {
                const qrSvg = qrBtn.getAttribute('data-qr-svg');
                if (qrSvg && qrSvg.trim() !== '') {
                    qrImgWrap.innerHTML = qrSvg;
                } else {
                    const apiQr = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(url);
                    qrImgWrap.innerHTML = `<img src="${apiQr}" alt="QR Code" class="img-fluid rounded content-qr-fallback-image">`;
                }
            }
            if (qrTitle) qrTitle.textContent = title;
            if (qrInput) qrInput.value = url;
            if (qrLink) qrLink.href = url;

            const modalEl = document.getElementById('contentQrModal');
            if (modalEl) {
                forceShowModal(modalEl);
            }
        }

        const detailQrBtnMobile = e.target.closest('.detail-qr-btn-mobile');
        if (detailQrBtnMobile) {
            e.preventDefault();
            const modalEl = document.getElementById('detailQrModal');
            if (modalEl) {
                forceShowModal(modalEl);
            }
        }
    });

    ['click', 'touchend'].forEach((eventType) => {
        document.addEventListener(eventType, (e) => {
            const closeBtn = e.target.closest('[data-bs-dismiss="modal"], [data-dismiss="modal"], .btn-close');
            if (closeBtn) {
                const modalEl = closeBtn.closest('.modal');
                if (modalEl) {
                    e.preventDefault();
                    forceHideModal(modalEl);
                }
            } else if (e.target.classList && e.target.classList.contains('modal')) {
                e.preventDefault();
                forceHideModal(e.target);
            }
        });
    });

    function forceHideModal(modalEl) {
        if (!modalEl) return;
        if (window.bootstrap && window.bootstrap.Modal) {
            const modalObj = window.bootstrap.Modal.getInstance(modalEl) || window.bootstrap.Modal.getOrCreateInstance(modalEl);
            if (modalObj) {
                try { modalObj.hide(); } catch (err) {}
            }
        }
        if (window.jQuery && window.jQuery.fn.modal) {
            try { window.jQuery(modalEl).modal('hide'); } catch (err) {}
        }
        modalEl.classList.remove('show');
        modalEl.style.removeProperty('display');
        modalEl.style.display = 'none';
        modalEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
    }

    function forceShowModal(modalEl) {
        if (!modalEl) return;
        if (modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
        modalEl.style.removeProperty('display');
        if (window.bootstrap && window.bootstrap.Modal) {
            const modalObj = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            if (modalObj) {
                try { modalObj.show(); } catch (err) {}
            }
        } else if (window.jQuery && window.jQuery.fn.modal) {
            try { window.jQuery(modalEl).modal('show'); } catch (err) {}
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
        }
    }

    queryAll('oembed').forEach((element) => {
        const rawUrl = String(element.getAttribute('url') || '').replace('watch?v=', 'embed/');

        try {
            const urlSrc = new URL(rawUrl, window.location.origin);
            const mediaFigure = query('figure.media');

            if ((urlSrc.protocol !== 'https:' && urlSrc.protocol !== 'http:') || !mediaFigure) return;

            const iframe = document.createElement('iframe');

            iframe.src = urlSrc.href;
            iframe.frameBorder = '0';
            iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
            iframe.allowFullscreen = true;
            iframe.style.width = '100%';
            iframe.style.height = `${window.innerHeight - 200}px`;

            mediaFigure.appendChild(iframe);
        } catch (error) {
            return;
        }
    });

    const setCollapseIcon = (target, expanded) => {
        const id = target && target.getAttribute('id');
        const toggleButton = id ? document.getElementById(`btn-${id}`) : null;
        const icon = toggleButton ? query('i', toggleButton) : null;

        if (!icon) return;

        icon.classList.toggle('fa-angle-double-down', !expanded);
        icon.classList.toggle('fa-angle-double-up', expanded);
    };

    document.addEventListener('shown.bs.collapse', (event) => setCollapseIcon(event.target, true));
    document.addEventListener('hidden.bs.collapse', (event) => setCollapseIcon(event.target, false));

    if (window.jQuery) {
        const jq = window.jQuery;

        jq('.collapse-detail')
            .on('shown.bs.collapse', (event) => setCollapseIcon(event.target, true))
            .on('hidden.bs.collapse', (event) => setCollapseIcon(event.target, false));
    }

    const showChatButton = query('#show-pchat');
    const hideChatButton = query('#show-pchat2');
    const setChatVisibility = (visible) => {
        queryAll('.s-chat').forEach((element) => {
            element.style.display = visible ? '' : 'none';
        });

        if (hideChatButton) {
            hideChatButton.style.display = visible ? 'none' : '';
        }
    };

    if (showChatButton) {
        showChatButton.addEventListener('click', () => setChatVisibility(false));
    }

    if (hideChatButton) {
        hideChatButton.addEventListener('click', () => setChatVisibility(true));
    }

    // Mobile navbar: use a real fullscreen menu so long/custom navbar
    // configurations never get clipped by the hero or the viewport edge.
    const mobileNavbarToggle = query('#rasamala-mobile-menu-toggle');
    const mobileNavbarClose = query('#rasamala-mobile-menu-close');
    const mobileNavbarPanel = query('#navbarSupportedContent');
    const mobileNavbarMedia = typeof window.matchMedia === 'function'
        ? window.matchMedia('(max-width: 991.98px)')
        : null;
    let mobileNavbarPreviousFocus = null;

    const isMobileNavbarViewport = () => mobileNavbarMedia
        ? mobileNavbarMedia.matches
        : window.innerWidth <= 991.98;
    const setMobileNavbarState = (open, restoreFocus = true) => {
        if (!mobileNavbarPanel || !mobileNavbarToggle) return;

        if (open && !isMobileNavbarViewport()) return;

        // Desktop keeps the normal navbar in the document flow and should
        // never inherit the mobile panel's aria-hidden/inert state.
        if (!open && !isMobileNavbarViewport()) {
            mobileNavbarPanel.classList.remove('rasamala-mobile-menu-open');
            mobileNavbarPanel.setAttribute('aria-hidden', 'false');
            mobileNavbarPanel.removeAttribute('inert');
            document.body.classList.remove('rasamala-mobile-menu-open');
            mobileNavbarToggle.setAttribute('aria-expanded', 'false');
            mobileNavbarToggle.classList.remove('is-active');
            return;
        }

        mobileNavbarPanel.classList.toggle('rasamala-mobile-menu-open', open);
        mobileNavbarPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (open) {
            mobileNavbarPanel.removeAttribute('inert');
            document.body.classList.add('rasamala-mobile-menu-open');
            mobileNavbarPreviousFocus = document.activeElement;
        } else {
            mobileNavbarPanel.setAttribute('inert', '');
            document.body.classList.remove('rasamala-mobile-menu-open');
        }
        mobileNavbarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        mobileNavbarToggle.classList.toggle('is-active', open);

        if (open) {
            window.requestAnimationFrame(() => {
                if (mobileNavbarClose) mobileNavbarClose.focus();
            });
        } else if (restoreFocus && mobileNavbarPreviousFocus
            && document.contains(mobileNavbarPreviousFocus)
            && typeof mobileNavbarPreviousFocus.focus === 'function') {
            mobileNavbarPreviousFocus.focus();
        }
    };

    if (mobileNavbarPanel && mobileNavbarToggle) {
        if (isMobileNavbarViewport()) {
            setMobileNavbarState(false, false);
        } else {
            mobileNavbarPanel.setAttribute('aria-hidden', 'false');
            mobileNavbarPanel.removeAttribute('inert');
        }

        mobileNavbarToggle.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = mobileNavbarPanel.classList.contains('rasamala-mobile-menu-open');
            setMobileNavbarState(!isOpen);
        });

        if (mobileNavbarClose) {
            mobileNavbarClose.addEventListener('click', () => setMobileNavbarState(false));
        }

        mobileNavbarPanel.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (!link || link.classList.contains('dropdown-toggle')) return;
            const href = (link.getAttribute('href') || '').trim();
            if (href && href !== '#') setMobileNavbarState(false, false);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mobileNavbarPanel.classList.contains('rasamala-mobile-menu-open')) {
                event.preventDefault();
                setMobileNavbarState(false);
            }
        });

        const syncMobileNavbarViewport = () => {
            if (!isMobileNavbarViewport()) setMobileNavbarState(false, false);
        };
        if (mobileNavbarMedia) {
            if (typeof mobileNavbarMedia.addEventListener === 'function') {
                mobileNavbarMedia.addEventListener('change', syncMobileNavbarViewport);
            } else if (typeof mobileNavbarMedia.addListener === 'function') {
                mobileNavbarMedia.addListener(syncMobileNavbarViewport);
            }
        }
    }

    const mobileMoreButtons = queryAll('[data-mobile-more-trigger]');
    const mobileMoreOverlay = query('#mobile-more-menu-overlay');
    const closeMobileMoreButton = query('#close-mobile-more-menu');
    const closeMobileMore = () => {
        if (mobileMoreOverlay) {
            mobileMoreOverlay.classList.remove('active');
        }

        document.body.classList.remove('overflow-hidden');
    };

    if (mobileMoreButtons.length && mobileMoreOverlay) {
        mobileMoreButtons.forEach((mobileMoreButton) => mobileMoreButton.addEventListener('click', (event) => {
            event.preventDefault();
            mobileMoreOverlay.classList.add('active');
            document.body.classList.add('overflow-hidden');
        }));

        mobileMoreOverlay.addEventListener('click', (event) => {
            if (event.target === mobileMoreOverlay) {
                closeMobileMore();
            }
        });
    }

    if (closeMobileMoreButton) {
        closeMobileMoreButton.addEventListener('click', closeMobileMore);
    }

    const backToTopButton = query('#back-to-top');

    if (backToTopButton) {
        const toggleBackToTop = () => {
            backToTopButton.classList.toggle('visible', window.pageYOffset > 300);
        };

        toggleBackToTop();
        window.addEventListener('scroll', toggleBackToTop, {passive: true});

        backToTopButton.addEventListener('click', (event) => {
            event.preventDefault();
            const reducedMotion = window.RasamalaMotionLifecycle
                && typeof window.RasamalaMotionLifecycle.prefersReducedMotion === 'function'
                && window.RasamalaMotionLifecycle.prefersReducedMotion();
            window.scrollTo({top: 0, behavior: reducedMotion ? 'auto' : 'smooth'});
        });
    }

    queryAll('.biblioPaging .pagingList').forEach((list) => {
        const walker = document.createTreeWalker(list, NodeFilter.SHOW_TEXT);
        const textNodes = [];

        while (walker.nextNode()) {
            textNodes.push(walker.currentNode);
        }

        textNodes.forEach((node) => {
            node.nodeValue = node.nodeValue.replace(/\u00a0/g, '');
        });
    });

    // Dynamic Running Text/Marquee Speed Adjustments (Constant speed in px/s)
    const adjustTickerMarqueeSpeeds = () => {
        const tickers = document.querySelectorAll('.latest-content-ticker');
        tickers.forEach(ticker => {
            const track = ticker.querySelector('.latest-content-ticker-track');
            const baseGroup = ticker.querySelector('.latest-content-ticker-group');

            if (!track || !baseGroup) return;

            ticker.style.setProperty('--ticker-start-gap', '0px');
            ticker.style.setProperty('--ticker-distance', '-50%');

            const speedKey = ticker.getAttribute('data-speed') || 'normal';
            let speedPxS = 65; // default normal
            if (speedKey === 'fast') {
                speedPxS = 110;
            } else if (speedKey === 'normal') {
                speedPxS = 65;
            } else if (speedKey === 'slow') {
                speedPxS = 36;
            } else if (speedKey === 'very_slow') {
                speedPxS = 20;
            }

            const tickerWidth = Math.max(ticker.clientWidth, 1);
            const baseContentWidth = Math.max(baseGroup.scrollWidth, 1);
            const shouldStartFromEdge = baseContentWidth < tickerWidth * 1.25;

            if (shouldStartFromEdge) {
                ticker.classList.add('is-edge-marquee');
                ticker.style.setProperty('--ticker-start-gap', `${tickerWidth}px`);
            }

            const baseGroupWidth = Math.max(baseGroup.scrollWidth, 1);
            const minimumTrackWidth = tickerWidth + (baseGroupWidth * 2);
            let cloneCount = 0;
            while (track.scrollWidth < minimumTrackWidth && cloneCount < 12) {
                const clone = baseGroup.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                clone.setAttribute('inert', '');
                clone.setAttribute('data-ticker-clone', 'true');
                track.appendChild(clone);
                cloneCount++;
            }

            const trackWidth = track.scrollWidth;
            ticker.style.setProperty('--ticker-distance', `-${baseGroupWidth}px`);
            const duration = (baseGroupWidth + (shouldStartFromEdge ? tickerWidth : 0)) / speedPxS;
            ticker.style.setProperty('--ticker-duration', `${duration}s`);
        });
    };

    const tickers = document.querySelectorAll('.latest-content-ticker');
    if ('ResizeObserver' in window) {
        const resizeObserver = new ResizeObserver(() => {
            adjustTickerMarqueeSpeeds();
        });

        tickers.forEach(ticker => {
            resizeObserver.observe(ticker);
            const track = ticker.querySelector('.latest-content-ticker-track');
            if (track) {
                resizeObserver.observe(track);
            }
        });
    } else {
        window.addEventListener('resize', adjustTickerMarqueeSpeeds);
        window.addEventListener('orientationchange', adjustTickerMarqueeSpeeds);
    }

    adjustTickerMarqueeSpeeds();

    // Simplify pagination by replacing text with FontAwesome icons
    const simplifyPagination = () => {
        const firstLinks = document.querySelectorAll('.pagingList .first_link');
        const prevLinks = document.querySelectorAll('.pagingList .prev_link');
        const nextLinks = document.querySelectorAll('.pagingList .next_link');
        const lastLinks = document.querySelectorAll('.pagingList .last_link');

        firstLinks.forEach(link => {
            const text = link.textContent.trim();
            link.setAttribute('title', text);
            link.setAttribute('aria-label', text);
            link.innerHTML = '<i class="fas fa-angle-double-left" aria-hidden="true"></i>';
        });

        prevLinks.forEach(link => {
            const text = link.textContent.trim();
            link.setAttribute('title', text);
            link.setAttribute('aria-label', text);
            link.innerHTML = '<i class="fas fa-chevron-left" aria-hidden="true"></i>';
        });

        nextLinks.forEach(link => {
            const text = link.textContent.trim();
            link.setAttribute('title', text);
            link.setAttribute('aria-label', text);
            link.innerHTML = '<i class="fas fa-chevron-right" aria-hidden="true"></i>';
        });

        lastLinks.forEach(link => {
            const text = link.textContent.trim();
            link.setAttribute('title', text);
            link.setAttribute('aria-label', text);
            link.innerHTML = '<i class="fas fa-angle-double-right" aria-hidden="true"></i>';
        });
    };
    simplifyPagination();
});

// Top Page Progress Bar Manager
(function () {
    const RasamalaProgressBar = {
        bar: null,
        timer: null,
        hideTimer: null,
        progress: 0,
        init() {
            if (this.bar) return;
            const el = document.createElement('div');
            el.id = 'rasamala-page-progress-bar';
            el.className = 'rasamala-page-progress-bar';
            el.setAttribute('role', 'progressbar');
            el.setAttribute('aria-hidden', 'true');
            document.body.appendChild(el);
            this.bar = el;
        },
        start() {
            this.init();
            if (!this.bar) return;
            if (this.bar.classList.contains('active')) return;
            clearTimeout(this.timer);
            clearTimeout(this.hideTimer);
            this.progress = 15;
            this.bar.style.transform = 'scaleX(0.15)';
            this.bar.classList.add('active');

            const trickle = () => {
                if (this.progress < 88) {
                    this.progress += Math.floor(Math.random() * 8) + 3;
                    this.bar.style.transform = `scaleX(${Math.min(this.progress, 88) / 100})`;
                    this.timer = setTimeout(trickle, 200);
                }
            };
            this.timer = setTimeout(trickle, 120);
        },
        done() {
            if (!this.bar) return;
            clearTimeout(this.timer);
            clearTimeout(this.hideTimer);
            this.bar.style.transform = 'scaleX(1)';
            this.hideTimer = setTimeout(() => {
                this.hideTimer = null;
                this.bar.classList.remove('active');
                this.timer = setTimeout(() => {
                    this.timer = null;
                    this.bar.style.transform = 'scaleX(0)';
                }, 300);
            }, 200);
        }
    };

    window.RasamalaProgressBar = RasamalaProgressBar;

    document.addEventListener('click', (event) => {
        const link = event.target && event.target.closest ? event.target.closest('a') : null;
        if (!link) return;

        const href = link.getAttribute('href');
        const target = link.getAttribute('target');
        const hasModal = link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-toggle');

        const isSamePageAnchor = href && (
            href.startsWith('#') || (
                !!link.hash &&
                link.origin === window.location.origin &&
                link.pathname.replace(/\/$/, '') === window.location.pathname.replace(/\/$/, '') &&
                link.search === window.location.search
            )
        );

        if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:') || target === '_blank' || hasModal || isSamePageAnchor) {
            return;
        }

        const isPaging = link.closest('.pagingList') || link.closest('.pagination') || link.classList.contains('page-link');
        const isBiblioLink = link.closest('.biblioResult') || link.closest('.biblio-item') || link.classList.contains('biblio-title-link');

        if (isPaging || isBiblioLink) {
            const wrapper = document.querySelector('.result-search .wrapper');
            if (wrapper) {
                if (wrapper.classList.contains('is-loading')) return;
                wrapper.classList.add('is-loading');
                const toolbar = document.querySelector('.search-result-toolbar') || document.querySelector('.result-search');
                if (toolbar) {
                    toolbar.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }

        RasamalaProgressBar.start();
    });

    window.addEventListener('pageshow', () => {
        document.querySelectorAll('.result-search .wrapper.is-loading').forEach((wrapper) => {
            wrapper.classList.remove('is-loading');
        });
        RasamalaProgressBar.done();
    }, {passive: true});

    window.addEventListener('beforeunload', () => {
        RasamalaProgressBar.start();
    });
})();
