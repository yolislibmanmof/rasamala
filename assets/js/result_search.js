/*!
 * Rasamala search result interactions.
 */
'use strict';

(function ($, window, document) {
    if (!$) {
        return;
    }

    const showModal = (modalId) => {
        const el = typeof modalId === 'string' ? document.getElementById(modalId) : modalId;
        if (!el) return;
        if (el.parentElement && el.parentElement !== document.body) {
            document.body.appendChild(el);
        }
        if (modalId === 'mobileSortModal' || (el && el.id === 'mobileSortModal')) {
            initSortControls();
        }
        document.body.classList.add('modal-open');
        $('.rasamala-sticky-action-wrapper, .rasamala-sticky-toolbar').css('z-index', '1');
        if (window.bootstrap && window.bootstrap.Modal) {
            try {
                const modal = window.bootstrap.Modal.getInstance(el) || new window.bootstrap.Modal(el);
                if (modal) {
                    modal.show();
                    return;
                }
            } catch (err) {}
        }
        if ($ && $.fn && $.fn.modal) {
            $(el).modal('show');
        }
    };

    const hideModal = (modalId) => {
        const el = typeof modalId === 'string' ? document.getElementById(modalId) : modalId;
        if (!el) return;
        $('.rasamala-sticky-action-wrapper, .rasamala-sticky-toolbar').css('z-index', '');
        if (window.bootstrap && window.bootstrap.Modal) {
            try {
                const modal = window.bootstrap.Modal.getInstance(el);
                if (modal) {
                    modal.hide();
                    return;
                }
            } catch (err) {}
        }
        if ($ && $.fn && $.fn.modal) {
            $(el).modal('hide');
        }
    };

    const normalizeSortText = (text) => {
        const label = String(text || '').replace(/[_-]/g, ' ').trim();

        return label ? label.charAt(0).toUpperCase() + label.slice(1) : '';
    };

    const closestElement = (target, selector) => {
        const element = target && target.nodeType === 1 ? target : target && target.parentElement;

        return element && element.closest ? element.closest(selector) : null;
    };

    const initGridLayout = () => {
        const $items = $('.biblioResult .grid-item');

        if ($items.length === 0 || !$.fn.masonry) {
            return;
        }

        const $grid = $('.biblioResult').addClass('row').masonry({
            itemSelector: '.grid-item',
            columnWidth: '.grid-item',
            percentPosition: true
        });

        $items.find('img').each(function () {
            const image = new Image();

            image.onload = image.onerror = function () {
                $grid.masonry('layout');
            };
            image.src = $(this).attr('src') || '';
        });

        if ($.fn.dropdown) {
            $('.dropdown-toggle').dropdown();
        }
    };

    const initAvailabilityPopover = () => {
        let activePopover = null;
        let activePinned = false;
        const canHover = window.matchMedia && window.matchMedia('(hover: hover)').matches;

        const closeActive = () => {
            if (activePopover) {
                activePopover.classList.remove('show');
            }
            activePopover = null;
            activePinned = false;
        };

        const openPopover = (popover, pinned) => {
            if (!popover) {
                return;
            }

            if (activePopover && activePopover !== popover) {
                activePopover.classList.remove('show');
            }

            popover.classList.add('show');
            activePopover = popover;
            activePinned = !!pinned;
        };

        document.addEventListener('click', (event) => {
            const badge = closestElement(event.target, '.biblio-avail-badge');

            if (badge) {
                const wrap = badge.closest('.biblio-avail-wrap');
                const popover = wrap ? wrap.querySelector('.biblio-avail-popover') : null;

                if (popover) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (popover.classList.contains('show') && activePopover === popover) {
                        closeActive();
                    } else {
                        openPopover(popover, true);
                    }

                    return;
                }
            }

            if (activePopover && !closestElement(event.target, '.biblio-avail-popover')) {
                closeActive();
            }
        });

        document.addEventListener('mouseover', (event) => {
            if (!canHover || activePinned) {
                return;
            }

            const badge = closestElement(event.target, '.biblio-avail-badge');
            if (!badge) {
                return;
            }

            const wrap = badge.closest('.biblio-avail-wrap');
            const popover = wrap ? wrap.querySelector('.biblio-avail-popover') : null;
            openPopover(popover, false);
        });

        document.addEventListener('mouseout', (event) => {
            if (!canHover) {
                return;
            }

            const wrap = closestElement(event.target, '.biblio-avail-wrap');
            if (!wrap || activePinned) {
                return;
            }

            if (event.relatedTarget && wrap.contains(event.relatedTarget)) {
                return;
            }

            const popover = wrap.querySelector('.biblio-avail-popover');
            if (popover && activePopover === popover) {
                closeActive();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeActive();
            }
        });
    };

    const getSortIcon = (value, text) => {
        const val = String(value || '').toLowerCase();
        const txt = String(text || '').toLowerCase();

        if (val.indexOf('title') !== -1 || txt.indexOf('title') !== -1 || txt.indexOf('judul') !== -1) {
            return (val.indexOf('desc') !== -1 || txt.indexOf('z-a') !== -1 || txt.indexOf('desc') !== -1) ? 'fas fa-sort-alpha-up' : 'fas fa-sort-alpha-down';
        }
        if (val.indexOf('year') !== -1 || txt.indexOf('year') !== -1 || txt.indexOf('tahun') !== -1 || txt.indexOf('publish') !== -1) {
            return 'fas fa-calendar-alt';
        }
        if (val.indexOf('date') !== -1 || val.indexOf('time') !== -1 || txt.indexOf('update') !== -1 || txt.indexOf('terbaru') !== -1) {
            return 'fas fa-clock';
        }
        if (val.indexOf('author') !== -1 || txt.indexOf('author') !== -1 || txt.indexOf('pengarang') !== -1) {
            return 'fas fa-user-edit';
        }
        return 'fas fa-sort-amount-down';
    };

    const createSortOption = (option) => {
        const value = option.value || '';
        const text = normalizeSortText(option.text);
        const selected = option.selected;
        const iconClass = getSortIcon(value, text);

        const $item = $('<a>', {
            href: '#',
            class: `list-group-item list-group-item-action search-control-option search-sort-option-item ${selected ? 'active' : ''}`,
            'data-value': value,
            'role': 'button'
        });

        const $left = $('<div>', { class: 'search-control-option-main' });
        const $iconBox = $('<div>', {
            class: 'search-control-option-icon sort-option-icon-box'
        }).append($('<i>', { class: iconClass, 'aria-hidden': 'true' }));

        const $textWrap = $('<div>', { class: 'search-control-option-text' });
        const $title = $('<div>', { class: 'search-control-option-title' }).text(text);
        $textWrap.append($title);
        $left.append($iconBox).append($textWrap);

        $item.append($left);

        if (selected) {
            $item.append($('<i>', { class: 'fas fa-check-circle search-control-option-check', 'aria-hidden': 'true' }));
        }

        return $item;
    };

    const updateSortLabel = () => {
        const $select = $('#search-order');
        if ($select.length === 0) return;
        const $selected = $select.find('option:selected');
        if ($selected.length > 0) {
            const cleanText = normalizeSortText($selected.text());
            if (cleanText) {
                $('#current-sort-label').text(cleanText);
            }
        }
    };

    const updateActiveFilterBadge = () => {
        const $checked = $('#mobile-filter-container #search-filter input[type="checkbox"]:checked, #mobile-filter-container #search-filter input[type="radio"]:checked');
        const count = $checked.length;
        const $badge = $('#active-filter-count');

        if (count > 0) {
            $badge.text(count).removeClass('d-none');
            $('#btn-open-filter-modal').addClass('is-active').attr('aria-pressed', 'true');
        } else {
            $badge.addClass('d-none');
            $('#btn-open-filter-modal').removeClass('is-active').attr('aria-pressed', 'false');
        }
    };

    const initSortControls = () => {
        const $select = $('#search-order');
        const $mobileOptions = $('#mobile-sort-options');

        if ($select.length === 0) {
            return;
        }

        $mobileOptions.empty();
        $select.find('option').each(function () {
            $mobileOptions.append(createSortOption(this));
        });

        updateSortLabel();

        $(document).off('click.sortOption', '#mobile-sort-options a').on('click.sortOption', '#mobile-sort-options a', function (event) {
            event.preventDefault();
            const val = $(this).attr('data-value') || $(this).data('value');
            if (val) {
                $select.val(val).trigger('change');
                updateSortLabel();
                hideModal('mobileSortModal');
                const searchParams = new URLSearchParams(window.location.search);
                searchParams.set('sortby', val);
                window.location.search = searchParams.toString();
            }
        });
    };

    const initMobileFilter = () => {
        $('#mobileFilterModal, #mobileSortModal, #mobileViewModal').appendTo('body');

        $(document).on('click', '#btn-open-filter-modal', function (e) {
            e.preventDefault();
            showModal('mobileFilterModal');
        });

        $(document).on('click', '#btn-open-sort-modal', function (e) {
            e.preventDefault();
            showModal('mobileSortModal');
        });

        $(document).on('click', '#btn-open-view-modal', function (e) {
            e.preventDefault();
            showModal('mobileViewModal');
        });

        updateActiveFilterBadge();

        // Ensure clicking any filter label / facet / chip toggles the checkbox or radio
        $(document).on('click', '#mobile-filter-container #search-filter label, #mobile-filter-container #search-filter .rasamala-filter-facet, #mobile-filter-container #search-filter .custom-control, #mobile-filter-container #search-filter .form-check, #mobile-filter-container #search-filter .badge', function (e) {
            const $target = $(e.target);
            if ($target.is('input')) {
                setTimeout(updateActiveFilterBadge, 50);
                return;
            }

            const $container = $(this);
            let $input = $container.find('input[type="checkbox"], input[type="radio"]');

            if ($input.length === 0 && $container.attr('for')) {
                const forId = $container.attr('for');
                $input = $(document.getElementById(forId));
            }

            if ($input.length > 0) {
                e.preventDefault();
                const isRadio = $input.attr('type') === 'radio';
                if (isRadio) {
                    const name = $input.attr('name');
                    if (name) {
                        $('#mobile-filter-container input[name="' + $.escapeSelector(name) + '"]').prop('checked', false).closest('label, .rasamala-filter-facet, .custom-control, .form-check').removeClass('active is-selected checked');
                    }
                    $input.prop('checked', true).trigger('change');
                    $container.addClass('active is-selected checked');
                } else {
                    const newChecked = !$input.prop('checked');
                    $input.prop('checked', newChecked).trigger('change');
                    $container.toggleClass('active is-selected checked', newChecked);
                }
                updateActiveFilterBadge();
            }
        });

        $(document).on('change', '#mobile-filter-container #search-filter input', function () {
            const $parent = $(this).closest('label, .rasamala-filter-facet, .custom-control, .form-check');
            if ($parent.length > 0) {
                $parent.toggleClass('active is-selected checked', $(this).prop('checked'));
            }
            updateActiveFilterBadge();
        });

        $('#mobileFilterModal').on('show.bs.modal', function () {
            updateActiveFilterBadge();
        });

        $('#mobileFilterModal').on('shown.bs.modal', function () {
            const slider = $('#mobile-filter-container #search-filter .input-slider').data('ionRangeSlider');

            if (slider) {
                slider.update();
            }
        });

        $('#apply-mobile-filter').on('click', function () {
            updateActiveFilterBadge();
            hideModal('mobileFilterModal');

            if (typeof window.filter === 'function') {
                window.filter();
            }
        });

        $('#reset-mobile-filter').on('click', function () {
            const $filter = $('#mobile-filter-container #search-filter');
            const $slider = $filter.find('.input-slider');
            const slider = $slider.data('ionRangeSlider');

            $filter.find('input[type="checkbox"]').prop('checked', false);
            $filter.find('input[type="radio"]').prop('checked', false);

            if (slider) {
                const minVal = $slider.data('min');
                const maxVal = $slider.data('max');

                slider.update({
                    from: minVal,
                    to: maxVal
                });

                $filter.find('.js-input-from').val(minVal);
                $filter.find('.js-input-to').val(maxVal);
            }

            updateActiveFilterBadge();
        });
    };

    const initResponsiveFilterPosition = () => {
        // Filter remains inside the pop-up modal on all screen sizes
    };

    const initResultLoadingState = () => {
        const triggerLoading = (scrollUp = false) => {
            const $wrapper = $('.result-search .wrapper');
            if ($wrapper.hasClass('is-loading')) return;

            $wrapper.addClass('is-loading');
            if (window.RasamalaProgressBar && typeof window.RasamalaProgressBar.start === 'function') {
                window.RasamalaProgressBar.start();
            }
            if (scrollUp) {
                const $toolbar = $('.rasamala-search-action-bar, .result-search');
                if ($toolbar.length > 0) {
                    const top = Math.max(0, $toolbar.offset().top - 80);
                    const reducedMotion = window.RasamalaMotionLifecycle
                        && typeof window.RasamalaMotionLifecycle.prefersReducedMotion === 'function'
                        && window.RasamalaMotionLifecycle.prefersReducedMotion();
                    if (reducedMotion) {
                        $('html, body').scrollTop(top);
                    } else {
                        $('html, body').animate({ scrollTop: top }, 250);
                    }
                }
            }
        };

        // 1. Pagination links click (Page 2, 3, Next, Prev, First, Last)
        $(document).on('click', '.pagination a, .pagingList a, .page-link, .page-item a', function () {
            const href = $(this).attr('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                triggerLoading(true);
            }
        });

        // 2. View mode switcher click
        $(document).on('click', '.search-view-option-item', function (event) {
            const href = $(this).attr('href');
            const viewVal = $(this).attr('data-view-value') || $(this).data('view-value');

            hideModal('mobileViewModal');

            if (href && href !== '#' && !href.startsWith('javascript:')) {
                event.preventDefault();
                window.location.href = href;
            } else if (viewVal) {
                event.preventDefault();
                $('#search-view-input-value').val(viewVal);
                const formEl = document.getElementById('search-view-mode-form');
                if (formEl) {
                    formEl.submit();
                } else {
                    const searchParams = new URLSearchParams(window.location.search);
                    searchParams.set('view', viewVal);
                    window.location.search = searchParams.toString();
                }
            }
        });

        $(document).on('submit', '#search-view-mode-form, .search-view-menu form', function () {
            hideModal('mobileViewModal');
            triggerLoading(false);
        });

        // 3. Desktop / Mobile Sort & Filter actions
        $(document).on('change', '#search-order', function () {
            triggerLoading(true);
        });

        $(document).on('click', '#apply-mobile-filter', function () {
            triggerLoading(true);
        });

        // 4. Detail links inside search results
        $(document).on('click', '.biblioResult a, .biblio-item a, .biblio-title-link, .result-item a', function () {
            const href = $(this).attr('href');
            const target = $(this).attr('target');
            if (href && href !== '#' && !href.startsWith('javascript:') && target !== '_blank' && !$(this).data('bs-toggle')) {
                triggerLoading(false);
            }
        });
    };

    $(function () {
        initGridLayout();
        initAvailabilityPopover();
        initSortControls();
        initMobileFilter();
        initResponsiveFilterPosition();
        initResultLoadingState();
    });
})(window.jQuery, window, document);
