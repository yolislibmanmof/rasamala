/**
 * @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
 * @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
 * @Date: 2026-08-06T07:43:00+07:00
 * @Filename: app.js
 */

'use strict';

// Vue 3 click-outside directive definition
const clickOutsideDirective = {
    beforeMount(el, binding) {
        el.clickOutsideEvent = function (event) {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.body.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.body.removeEventListener('click', el.clickOutsideEvent);
    }
};

const allowedSearchFields = ['keywords', 'author', 'subject', 'isbn'];
const makeSearchUrl = function (searchBy, text) {
    const field = allowedSearchFields.indexOf(searchBy) !== -1 ? searchBy : 'keywords';
    const params = new URLSearchParams();
    params.set(field, text || '');
    params.set('search', 'search');
    return `index.php?${params.toString()}`;
};
const searchHistoryStorageKey = 'keywords';
const searchHistoryLimit = 25;
const searchHistoryKeywordMaxLength = 120;
const normalizeSearchKeyword = function (value) {
    const keyword = String(value || '').replace(/\s+/g, ' ').trim();
    return keyword.length > 0 && keyword.length <= searchHistoryKeywordMaxLength ? keyword : '';
};
const normalizeSearchHistory = function (history) {
    if (!history || typeof history !== 'object' || Array.isArray(history)) {
        return {};
    }

    const entries = [];
    Object.keys(history).forEach(key => {
        const item = history[key];
        const text = normalizeSearchKeyword(key);
        if (!text || !item || typeof item !== 'object') {
            return;
        }

        const time = Number(item.time);
        if (!Number.isFinite(time) || time < 0) {
            return;
        }

        const count = Number(item.count);
        const searchBy = allowedSearchFields.indexOf(item.searchBy) !== -1 ? item.searchBy : 'keywords';
        entries.push({
            text: text,
            count: Number.isFinite(count) ? Math.max(1, Math.min(9999, Math.floor(count))) : 1,
            searchBy: searchBy,
            time: time
        });
    });

    entries.sort((a, b) => b.time - a.time);
    return entries.slice(0, searchHistoryLimit).reduce((result, item) => {
        result[item.text] = {
            count: item.count,
            searchBy: item.searchBy,
            time: item.time
        };
        return result;
    }, {});
};
const readSearchHistory = function () {
    try {
        if (!window.localStorage) {
            return {};
        }
        const raw = window.localStorage.getItem(searchHistoryStorageKey);
        if (!raw || raw.length > 20000) {
            return {};
        }
        return normalizeSearchHistory(JSON.parse(raw));
    } catch (e) {
        return {};
    }
};
const writeSearchHistory = function (history) {
    try {
        if (!window.localStorage) {
            return;
        }
        window.localStorage.setItem(searchHistoryStorageKey, JSON.stringify(normalizeSearchHistory(history)));
    } catch (e) {}
};
const fetchJsonArray = function (url) {
    return fetch(url, {headers: {'Accept': 'application/json'}})
        .then(res => {
            if (!res.ok) {
                throw new Error(`Request failed with status ${res.status}`);
            }
            return res.text().then(text => ({
                text: text
            }));
        })
        .then(payload => {
            var parsed;
            try {
                parsed = JSON.parse(payload.text);
            } catch (error) {
                // Some SLiMS installations return the full OPAC HTML for the
                // popular endpoint when there are no loan rows. Fall back to
                // latest collections instead of leaking an HTML parse error.
                if (String(url || '').indexOf('api/biblio/popular') !== -1) {
                    return fetchJsonArray('index.php?p=api/biblio/latest');
                }
                throw new Error('The collection endpoint did not return JSON.');
            }
            return Array.isArray(parsed) ? parsed : [];
        });
};
const renderAsyncMessage = function (h, type, message, onRetry) {
    const children = [
        h('div', { class: 'theme-async-message text-muted small fw-medium' }, message)
    ];

    if (type === 'error' && typeof onRetry === 'function') {
        children.unshift(
            h('div', { class: 'theme-async-error-icon mb-2' }, [
                h('i', { class: 'fas fa-exclamation-circle text-warning fs-3', 'aria-hidden': 'true' })
            ])
        );
        children.push(
            h('button', {
                type: 'button',
                class: 'btn btn-outline-primary btn-sm rounded-pill mt-3 px-4 shadow-sm theme-async-retry-btn',
                onClick: onRetry
            }, [
                h('i', { class: 'fas fa-redo-alt me-2', 'aria-hidden': 'true' }),
                'Coba Lagi'
            ])
        );
    }

    return h('div', {
        class: `theme-async-state theme-async-state-${type} theme-async-message-wrap text-center py-4 px-3 rounded-4 border shadow-sm my-3 mx-auto`,
        role: type === 'error' ? 'alert' : 'status',
        'aria-live': 'polite'
    }, children);
};
const renderSkeleton = function (h, type, count) {
    const items = [];
    for (let i = 0; i < count; i++) {
        if (type === 'collection') {
            items.push(h('div', {
                key: `skeleton-col-${i}`,
                class: 'col-6 col-sm-4 col-md-3 col-lg-2 pb-4'
            }, [
                h('div', {
                    class: 'card border-0 shadow-sm h-full theme-skeleton-card theme-skeleton-book-card'
                }, [
                    h('div', {
                        class: 'card-body p-2'
                    }, [
                        h('div', {
                            class: 'card-image fit-height theme-skeleton-cover-box'
                        }, [
                            h('div', { class: 'theme-skeleton-block theme-skeleton-cover' })
                        ]),
                        h('div', {
                            class: 'card-text mt-2'
                        }, [
                            h('div', { class: 'theme-skeleton-block theme-skeleton-line theme-skeleton-line-title mb-1' }),
                            h('div', { class: 'theme-skeleton-block theme-skeleton-line theme-skeleton-line-sub' })
                        ])
                    ])
                ])
            ]));
        } else if (type === 'member') {
            items.push(h('div', {
                key: `skeleton-mem-${i}`,
                class: 'col-12 col-md-4 mb-4'
            }, [
                h('div', {
                    class: 'card member-card theme-skeleton-card theme-skeleton-member-card'
                }, [
                    h('div', {
                        class: 'card-body text-center p-4'
                    }, [
                        h('div', {
                            class: 'card-image-rounded mx-auto theme-skeleton-avatar-box'
                        }, [
                            h('div', { class: 'theme-skeleton-block theme-skeleton-avatar rounded-circle' })
                        ]),
                        h('div', {
                            class: 'mt-3 text-center d-flex flex-column align-items-center gap-2'
                        }, [
                            h('div', { class: 'theme-skeleton-block theme-skeleton-line theme-skeleton-line-name mx-auto' }),
                            h('div', { class: 'theme-skeleton-block theme-skeleton-line theme-skeleton-line-sub mx-auto mt-1' })
                        ]),
                        h('div', {
                            class: 'mt-3 d-flex justify-content-center gap-3'
                        }, [
                            h('div', { class: 'theme-skeleton-block theme-skeleton-line theme-skeleton-line-stat' }),
                            h('div', { class: 'theme-skeleton-block theme-skeleton-line theme-skeleton-line-stat' })
                        ])
                    ])
                ])
            ]));
        } else if (type === 'subject') {
            items.push(h('div', {
                key: `skeleton-sub-${i}`,
                class: 'theme-skeleton-badge mr-2 mb-2'
            }, [
                h('div', { class: 'theme-skeleton-block theme-skeleton-badge-pill' })
            ]));
        } else {
            items.push(h('div', {
                key: `skeleton-${type}-${i}`,
                class: `theme-skeleton-item theme-skeleton-item-${type}`
            }, [
                h('div', { class: 'theme-skeleton-block' })
            ]));
        }
    }

    let wrapperClass = `theme-skeleton-list theme-skeleton-list-${type}`;
    if (type === 'collection') {
        wrapperClass = 'row mt-4 collection justify-content-center theme-skeleton-list theme-skeleton-list-collection';
    } else if (type === 'member') {
        wrapperClass = 'row justify-content-center theme-skeleton-list theme-skeleton-list-member';
    } else if (type === 'subject') {
        wrapperClass = 'd-flex flex-row flex-wrap justify-content-center mb-3 rasamala-group-subject theme-skeleton-list theme-skeleton-list-subject';
    }

    return h('div', {
        class: wrapperClass,
        role: 'status',
        'aria-live': 'polite'
    }, [
        h('span', { class: 'sr-only' }, 'Loading...'),
        ...items
    ]);
};
const unescapeSlashes = function (str) {
    return String(str || '').replace(/\\(['"\\])/g, '$1');
};
const getParallelTitleSeparator = function () {
    const separator = String(window.rasamalaParallelTitleSeparator || '').trim();
    if (separator === '0' || separator.toLowerCase() === 'none') {
        return '';
    }
    return separator;
};
const splitParallelTitle = function (title) {
    const rawTitle = unescapeSlashes(String(title || '').trim());
    const separator = getParallelTitleSeparator();
    const separatorIndex = separator ? rawTitle.indexOf(separator) : -1;

    if (separatorIndex < 0) {
        return {
            main: rawTitle,
            parallel: ''
        };
    }

    const main = rawTitle.slice(0, separatorIndex).trim();
    const parallel = rawTitle.slice(separatorIndex + separator.length).trim();

    if (!main || !parallel) {
        return {
            main: rawTitle,
            parallel: ''
        };
    }

    return {
        main: main,
        parallel: parallel
    };
};
const getTitleCharacterLimit = function () {
    const limit = parseInt(window.rasamalaTitleCharacterLimit, 10);
    if (!Number.isFinite(limit)) {
        return 100;
    }

    return Math.max(1, Math.min(300, limit));
};
const limitTitleText = function (title, limit) {
    const cleanTitle = unescapeSlashes(String(title || '').trim());
    const safeLimit = typeof limit === 'number' ? Math.max(1, Math.min(300, limit)) : getTitleCharacterLimit();
    const suffix = '...';
    const chars = Array.from(cleanTitle);

    if (chars.length <= safeLimit) {
        return cleanTitle;
    }

    return chars.slice(0, Math.max(0, safeLimit - suffix.length)).join('').replace(/\s+$/, '') + suffix;
};
const getAutoCoverMode = function () {
    const mode = String(window.rasamalaAutoCoverMode || '').trim();
    if (['empty_missing', 'empty_only', 'none'].indexOf(mode) !== -1) {
        return mode;
    }

    return window.rasamalaAutoCoverGenerator === false ? 'none' : 'empty_missing';
};
const getCoverState = function (imageUrl, imageLoadError) {
    if (imageLoadError) {
        return 'missing';
    }

    const value = String(imageUrl || '').trim();
    const lowerValue = value.toLowerCase();
    if (!value ||
        lowerValue.indexOf('default/image.png') !== -1 ||
        lowerValue.indexOf('no-image') !== -1 ||
        lowerValue.indexOf('no-cover') !== -1) {
        return 'empty';
    }

    if (lowerValue.indexOf('notfound') !== -1 ||
        lowerValue.indexOf('not-found') !== -1 ||
        lowerValue.indexOf('file-not-found') !== -1) {
        return 'missing';
    }

    return 'valid';
};
const shouldGenerateAutoCover = function (imageUrl, imageLoadError) {
    const mode = getAutoCoverMode();
    const state = getCoverState(imageUrl, imageLoadError);
    if (mode === 'none') {
        return false;
    }
    if (mode === 'empty_only') {
        return state === 'empty';
    }

    return state === 'empty' || state === 'missing';
};

// Component Definitions
const SlimsSubject = {
    props: {
        topic: {
            type: String,
            default: ''
        }
    },
    render() {
        return Vue.h('a', {
            href: `index.php?subject="${encodeURIComponent(this.topic).replace(/%20/g, "+")}"&search=search`,
            class: 'btn btn-outline-secondary btn-rounded btn-sm mr-2 mb-2'
        }, this.topic);
    }
};

const SlimsBook = {
    props: {
        biblioId: {
            type: String,
            default: ''
        },
        title: {
            type: String,
            default: ''
        },
        image: {
            type: String,
            default: ''
        },
        isPopular: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            imageLoadError: false
        };
    },
    render() {
        const titleParts = splitParallelTitle(this.title);
        const titleLimit = getTitleCharacterLimit();
        const titleChildren = [
            Vue.h('span', {
                class: 'parallel-title-main'
            }, limitTitleText(titleParts.main, titleLimit))
        ];

        if (titleParts.parallel) {
            titleChildren.push(Vue.h('span', {
                class: 'parallel-title-alt'
            }, [
                Vue.h('i', {
                    class: 'fas fa-language',
                    'aria-hidden': 'true'
                }),
                limitTitleText(titleParts.parallel, titleLimit)
            ]));
        }

        const imageUrl = String(this.image || '').trim();
        const fallbackImageUrl = 'lib/minigallery/file-not-found.png';

        let imageElement;
        if (!this.isPopular && shouldGenerateAutoCover(imageUrl, this.imageLoadError)) {
            let titleHash = 0;
            const cleanText = String(titleParts.main || '').trim();
            for (let i = 0; i < cleanText.length; i++) {
                titleHash = cleanText.charCodeAt(i) + ((titleHash << 5) - titleHash);
            }
            const gradientIndex = Math.abs(titleHash) % 6;

            imageElement = Vue.h('div', {
                class: `book-cover-placeholder book-cover-gradient-${gradientIndex}`,
                'aria-hidden': 'true'
            }, [
                Vue.h('div', {
                    class: 'book-cover-content'
                }, [
                    Vue.h('div', {
                        class: 'book-cover-header-text'
                    }, 'COLLECTION'),
                    Vue.h('div', {
                        class: 'book-cover-title-text'
                    }, limitTitleText(titleParts.main, 40)),
                    Vue.h('div', {
                        class: 'book-cover-footer-text'
                    }, 'FEB UI')
                ])
            ]);
        } else {
            imageElement = Vue.h('img', {
                src: this.imageLoadError ? fallbackImageUrl : (this.image || fallbackImageUrl),
                class: 'img-fluid',
                loading: 'lazy',
                alt: this.title ? `Cover of ${this.title}` : 'Collection cover',
                onError: () => {
                    if (!this.imageLoadError) {
                        this.imageLoadError = true;
                    }
                }
            });
        }

        return Vue.h('div', {
            class: 'col-6 col-sm-4 col-md-3 col-lg-2 pb-4'
        }, [
            Vue.h('a', {
                href: `index.php?p=show_detail&id=${this.biblioId}`,
                class: 'card border-0 shadow-sm cursor-pointer text-decoration-none h-full slims-book-card'
            }, [
                Vue.h('div', {
                    class: 'card-body'
                }, [
                    Vue.h('div', {
                        class: 'card-image fit-height'
                    }, [
                        imageElement
                    ]),
                    Vue.h('div', {
                        class: 'card-text mt-2 parallel-title parallel-title-home'
                    }, titleChildren)
                ])
            ])
        ]);
    }
};

const SlimsMember = {
    props: {
        image: {
            type: String,
            default: ''
        },
        memberName: {
            type: String,
            default: ''
        },
        memberType: {
            type: String,
            default: ''
        },
        totalLoan: {
            type: String,
            default: '0'
        },
        totalBiblio: {
            type: String,
            default: '0'
        }
    },
    render() {
        const imageUrl = String(this.image || '').trim();
        const isPlaceholder = !imageUrl || 
                              imageUrl.indexOf('default/image.png') !== -1 || 
                              imageUrl.indexOf('notfound') !== -1 ||
                              imageUrl.indexOf('not-found') !== -1 ||
                              imageUrl.indexOf('file-not-found') !== -1 ||
                              imageUrl.indexOf('no-image') !== -1 ||
                              imageUrl.indexOf('no-avatar') !== -1;

        let avatarElement;
        if (isPlaceholder) {
            const nameParts = String(this.memberName || '').trim().split(/\s+/);
            let initials = '';
            if (nameParts.length > 0) {
                initials += nameParts[0].charAt(0).toUpperCase();
                if (nameParts.length > 1) {
                    initials += nameParts[nameParts.length - 1].charAt(0).toUpperCase();
                }
            }
            if (!initials) initials = '?';

            let nameHash = 0;
            const cleanName = String(this.memberName || '').trim();
            for (let i = 0; i < cleanName.length; i++) {
                nameHash = cleanName.charCodeAt(i) + ((nameHash << 5) - nameHash);
            }
            const avatarGradientIndex = Math.abs(nameHash) % 5;

            avatarElement = Vue.h('div', {
                class: `member-avatar-placeholder member-avatar-gradient-${avatarGradientIndex}`,
                'aria-label': this.memberName ? `Initials for ${this.memberName}` : 'Member initials',
                role: 'img'
            }, initials);
        } else {
            avatarElement = Vue.h('img', {
                class: 'img-fluid h-auto',
                src: this.image,
                loading: 'lazy',
                alt: this.memberName ? `Avatar of ${this.memberName}` : 'Member avatar'
            });
        }

        return Vue.h('div', {
            class: 'col-12 col-md-4 mb-4'
        }, [
            Vue.h('div', {
                class: 'card hover:shadow-md member-card'
            }, [
                Vue.h('div', {
                    class: 'card-body'
                }, [
                    Vue.h('div', {
                        class: 'card-image-rounded mx-auto'
                    }, [
                        avatarElement
                    ]),
                    Vue.h('h3', {
                        class: 'card-title text-center mt-3'
                    }, [
                        Vue.h('span', this.memberName),
                        Vue.h('br'),
                        Vue.h('small', {
                            class: 'text-secondary'
                        }, this.memberType)
                    ]),
                    Vue.h('p', {
                        class: 'card-text text-center'
                    }, [
                        Vue.h('b', this.totalLoan),
                        Vue.h('span', {
                            class: 'text-secondary ml-1'
                        }, 'Loans'),
                        Vue.h('span', {
                            class: 'd-inline-block mx-3 align-middle bg-secondary theme-stat-divider'
                        }),
                        Vue.h('b', this.totalBiblio),
                        Vue.h('span', {
                            class: 'text-secondary ml-1'
                        }, 'Title'),
                    ])
                ])
            ])
        ]);
    }
};

const SlimsCollection = {
    props: {
        url: {
            type: String,
            default: ''
        },
        limit: {
            type: [Number, String],
            default: 6
        }
    },
    data() {
        return {
            biblios: [],
            loading: false,
            error: ''
        };
    },
    mounted() {
        this.getData();
    },
    methods: {
        getData() {
            this.loading = true;
            this.error = '';
            fetchJsonArray(this.url)
                .then(res => {
                    this.biblios = res;
                })
                .catch(err => {
                    console.error(err.message);
                    this.error = 'Unable to load collections.';
                    this.biblios = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    render() {
        var limitVal = parseInt(this.limit) || 6;
        if (this.loading && this.biblios.length < 1) {
            return renderSkeleton(Vue.h, 'collection', limitVal);
        }
        if (this.error) {
            return renderAsyncMessage(Vue.h, 'error', this.error, () => this.getData());
        }
        if (this.biblios.length < 1) {
            return renderAsyncMessage(Vue.h, 'empty', 'No collections available yet.');
        }

        var isPopular = String(this.url || '').indexOf('/popular') !== -1;
        var items = this.biblios.slice(0, limitVal);
        return Vue.h('div', {
            class: 'row mt-4 collection justify-content-center'
        }, items.map(item => {
            return Vue.h(SlimsBook, {
                key: item.biblio_id,
                biblioId: item.biblio_id,
                image: item.image,
                title: item.title,
                isPopular: isPopular
            });
        }));
    }
};

const SlimsGroupSubject = {
    props: {
        url: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            subjects: [],
            loading: false,
            error: ''
        };
    },
    mounted() {
        this.getData();
    },
    methods: {
        getData() {
            this.loading = true;
            this.error = '';
            fetchJsonArray(this.url)
                .then(res => {
                    this.subjects = res;
                })
                .catch(err => {
                    console.error(err.message);
                    this.error = 'Unable to load topics.';
                    this.subjects = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    render() {
        if (this.loading && this.subjects.length < 1) {
            return renderSkeleton(Vue.h, 'subject', 8);
        }
        if (this.error) {
            return renderAsyncMessage(Vue.h, 'error', this.error, () => this.getData());
        }
        if (this.subjects.length < 1) {
            return renderAsyncMessage(Vue.h, 'empty', 'No topics available yet.');
        }

        return Vue.h('div', {
            class: 'd-flex flex-row flex-wrap justify-content-center mb-3 rasamala-group-subject'
        }, this.subjects.map(topic => {
            return Vue.h(SlimsSubject, {
                key: topic,
                topic: topic
            });
        }));
    }
};

const SlimsGroupMember = {
    props: {
        url: {
            type: String,
            default: ''
        },
        limit: {
            type: [Number, String],
            default: 3
        }
    },
    data() {
        return {
            members: [],
            loading: false,
            error: ''
        };
    },
    mounted() {
        this.getData();
    },
    methods: {
        getData() {
            this.loading = true;
            this.error = '';
            fetchJsonArray(this.url)
                .then(res => {
                    this.members = res;
                })
                .catch(err => {
                    console.error(err.message);
                    this.error = 'Unable to load top readers.';
                    this.members = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    },
    render() {
        var limitVal = parseInt(this.limit) || 3;
        if (this.loading && this.members.length < 1) {
            return renderSkeleton(Vue.h, 'member', limitVal);
        }
        if (this.error) {
            return renderAsyncMessage(Vue.h, 'error', this.error, () => this.getData());
        }
        if (this.members.length < 1) {
            return renderAsyncMessage(Vue.h, 'empty', 'No top readers available yet.');
        }

        var items = this.members.slice(0, limitVal);
        return Vue.h('div', {
            class: 'row justify-content-center'
        }, items.map(member => {
            return Vue.h(SlimsMember, {
                key: member.member_id || member.name,
                memberName: member.name,
                memberType: member.type,
                image: member.image,
                totalLoan: member.total,
                totalBiblio: member.total_title
            });
        }));
    }
};

// The fullscreen hero sits outside #slims-home, so its collection/member
// cards need a small Vue mount of their own. Keeping this renderer lazy means
// only the selected hero section performs an API request.
const mountRasamalaHeroInside = function(mount, type) {
    if (!mount || !window.Vue) return null;
    if (mount._rasamalaHeroApp) {
        mount._rasamalaHeroApp.unmount();
        mount._rasamalaHeroApp = null;
    }
    mount._rasamalaHeroType = null;
    mount.textContent = '';

    const data = {
        popular: {
            title: 'Popular among our collections',
            icon: 'fas fa-fire',
            subjectUrl: 'index.php?p=api/subject/popular',
            collectionUrl: 'index.php?p=api/biblio/popular',
            limit: parseInt(mount.getAttribute('data-popular-limit'), 10) || 6
        },
        new_update: {
            title: 'New collections + updated',
            icon: 'fas fa-book',
            subjectUrl: 'index.php?p=api/subject/latest',
            collectionUrl: 'index.php?p=api/biblio/latest',
            limit: parseInt(mount.getAttribute('data-new-limit'), 10) || 6
        },
        top_reader: {
            title: 'Top reader of the year',
            icon: 'fas fa-trophy',
            memberUrl: 'index.php?p=api/member/top',
            limit: parseInt(mount.getAttribute('data-top-reader-limit'), 10) || 5
        }
    }[type];
    if (!data) return null;

    const app = Vue.createApp({
        render() {
            const children = [
                Vue.h('h2', {class: 'rasamala-hero-inline-title'}, [
                    Vue.h('i', {class: data.icon, 'aria-hidden': 'true'}),
                    ' ' + data.title
                ])
            ];
            if (type === 'top_reader') {
                children.push(Vue.h(SlimsGroupMember, {url: data.memberUrl, limit: data.limit}));
            } else {
                children.push(Vue.h(SlimsGroupSubject, {url: data.subjectUrl}));
                children.push(Vue.h(SlimsCollection, {url: data.collectionUrl, limit: data.limit}));
            }
            return Vue.h('div', {class: 'rasamala-hero-inline-section'}, children);
        }
    });
    app.component('slims-book', SlimsBook);
    app.component('slims-member', SlimsMember);
    app.component('slims-collection', SlimsCollection);
    app.component('slims-group-subject', SlimsGroupSubject);
    app.component('slims-group-member', SlimsGroupMember);
    app.mount(mount);
    mount._rasamalaHeroApp = app;
    mount._rasamalaHeroType = type;
    return app;
};

window.RasamalaHeroRenderer = {
    mount: mountRasamalaHeroInside,
    clear: function(mount) {
        if (!mount) return;
        if (mount._rasamalaHeroApp) {
            mount._rasamalaHeroApp.unmount();
            mount._rasamalaHeroApp = null;
        }
        mount._rasamalaHeroType = null;
        mount.removeAttribute('data-hero-mounted-type');
        mount.textContent = '';
    }
};

// Non-Theme-Viewer pages still need the saved hero selection to render.
(function mountSavedHeroInside() {
    const root = document.getElementById('rasamala-hero-inside-content');
    if (!root) return;
    // Theme Viewer owns the initial mount when its lazy templates are present.
    if (root.querySelector('#rasamala-hero-inside-templates')) return;
    const activeItem = root.querySelector('.rasamala-hero-inside-item:not([hidden])');
    const type = activeItem && activeItem.getAttribute('data-inside');
    const mount = activeItem && activeItem.querySelector('[data-hero-inside-mount]');
    if (mount && type && type !== 'topics') mountRasamalaHeroInside(mount, type);
}());

// Initialize showAdvancedApp Vue instance if element exists
if (document.getElementById('search-wraper')) {
    const showAdvancedApp = Vue.createApp({
        data() {
            return {
                show: false,
                isFocus: false,
                searchBy: 'keywords',
                keywords: '',
                tmpObj: {},
                isProgrammaticFocus: false,
                loading: false,
                suggestions: [],
                suggestLoading: false,
                showSuggestions: false,
                selectedIndex: -1,
                debounceTimer: null,
                suggestionController: null,
                suggestRequestId: 0,
                searchNavigationTimer: null,
                onSearchPageShow: null,
                historyVersion: 0
            };
        },
        computed: {
            lastKeywords() {
                const _ver = this.historyVersion;
                let keywords = readSearchHistory(), arr = [];
                this.tmpObj = {};
                for (let key in keywords) {
                    if (Object.prototype.hasOwnProperty.call(keywords, key)) {
                        arr.push(keywords[key].time);
                        keywords[key].text = key;
                        this.tmpObj[keywords[key].time] = keywords[key];
                    }
                }
                arr.sort((a, b) => b - a);
                return arr.slice(0, 5);
            }
        },
        watch: {
            keywords(newVal) {
                this.selectedIndex = -1;
                this.handleKeywordChange(newVal);
            }
        },
        mounted() {
            if (this.$refs.keywords && window.innerWidth >= 768) {
                this.isProgrammaticFocus = true;
                this.$refs.keywords.focus();
            }
            this.onSearchPageShow = () => this.resetSearchLoading();
            window.addEventListener('pageshow', this.onSearchPageShow);
        },
        beforeUnmount() {
            window.removeEventListener('pageshow', this.onSearchPageShow);
            this.resetSearchLoading();
        },
        methods: {
            searchOnFocus(e) {
                this.isFocus = true;
                if (this.isProgrammaticFocus) {
                    // Focused programmatically on initial page load / navigation:
                    // Keep cursor focused in search box, but DO NOT show search history dropdown!
                    this.showSuggestions = false;
                } else {
                    this.show = true;
                    if (this.keywords.length >= 2) {
                        this.showSuggestions = true;
                    } else {
                        this.showSuggestions = false;
                    }
                }
            },
            searchOnClickArea(e) {
                // User manually clicked/tapped inside search input or search area
                this.isProgrammaticFocus = false;
                this.isFocus = true;
                this.show = true;
                if (this.keywords === '' && this.lastKeywords.length > 0) {
                    this.showSuggestions = true;
                } else if (this.keywords.length >= 2) {
                    this.showSuggestions = true;
                }
            },
            searchOnBlur(e) {
                this.isFocus = false;
            },
            hideSearch() {
                if (!this.isFocus) {
                    this.show = false;
                    this.searchBy = 'keywords';
                }
            },
            hideSuggestions() {
                this.showSuggestions = false;
            },
            handleKeywordChange(val) {
                const query = String(val || '').trim();
                const requestId = ++this.suggestRequestId;
                if (this.debounceTimer) {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = null;
                }
                if (this.suggestionController) {
                    this.suggestionController.abort();
                    this.suggestionController = null;
                }

                if (query.length < 2) {
                    this.suggestions = [];
                    this.suggestLoading = false;
                    this.showSuggestions = !this.isProgrammaticFocus && this.isFocus && (query === '' && this.lastKeywords.length > 0);
                    return;
                }

                this.isProgrammaticFocus = false;
                this.suggestLoading = true;
                this.showSuggestions = true;

                this.debounceTimer = setTimeout(() => {
                    this.fetchLiveSuggestions(query, requestId);
                }, 250);
            },
            fetchLiveSuggestions(query, requestId) {
                if (requestId !== this.suggestRequestId) return;

                const historyMatches = [];
                const historyData = readSearchHistory();
                for (let key in historyData) {
                    if (key.toLowerCase().indexOf(query.toLowerCase()) !== -1) {
                        historyMatches.push({
                            text: key,
                            type: 'history',
                            searchBy: historyData[key].searchBy || 'keywords',
                            icon: 'fas fa-history'
                        });
                    }
                }

                const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
                this.suggestionController = controller;
                const fetchUrl = `index.php?rasamala_suggest=1&q=${encodeURIComponent(query)}`;
                const requestOptions = {
                    headers: { 'Accept': 'application/json' }
                };
                if (controller) requestOptions.signal = controller.signal;

                let timeoutId = null;
                const timeoutPromise = new Promise((resolve, reject) => {
                    timeoutId = setTimeout(() => {
                        if (controller) controller.abort();
                        const timeoutError = new Error('Suggestion request timed out');
                        timeoutError.name = 'TimeoutError';
                        reject(timeoutError);
                    }, 4000);
                });

                const requestPromise = fetch(fetchUrl, requestOptions)
                    .then(res => {
                        if (!res.ok) throw new Error('Suggestion request failed');
                        return res.text().then(text => {
                            try {
                                const parsed = JSON.parse(text);
                                return Array.isArray(parsed) ? parsed : [];
                            } catch (error) {
                                return [];
                            }
                        });
                    });

                Promise.race([requestPromise, timeoutPromise])
                    .then(items => {
                        if (requestId !== this.suggestRequestId) return;

                        const liveItems = [];
                        const seen = new Set(historyMatches.map(h => h.text.toLowerCase()));
                        (Array.isArray(items) ? items : []).forEach(item => {
                            const text = String(item && item.title ? item.title : '').trim();
                            if (text && !seen.has(text.toLowerCase()) && liveItems.length < 5) {
                                seen.add(text.toLowerCase());
                                liveItems.push({
                                    text: text,
                                    type: 'title',
                                    searchBy: 'keywords',
                                    icon: 'fas fa-book'
                                });
                            }
                        });

                        this.suggestions = [...historyMatches.slice(0, 3), ...liveItems].slice(0, 8);
                    })
                    .catch(error => {
                        if (error && error.name === 'AbortError') return;
                        if (requestId !== this.suggestRequestId) return;
                        this.suggestions = historyMatches.slice(0, 5);
                    })
                    .finally(() => {
                        if (timeoutId) clearTimeout(timeoutId);
                        if (requestId !== this.suggestRequestId) return;
                        this.suggestLoading = false;
                        this.suggestionController = null;
                    });
            },
            navigateSuggestions(direction) {
                if (!this.showSuggestions) {
                    this.showSuggestions = true;
                    return;
                }
                const total = this.suggestions.length > 0 ? this.suggestions.length : this.lastKeywords.length;
                if (total === 0) return;

                this.selectedIndex += direction;
                if (this.selectedIndex >= total) {
                    this.selectedIndex = 0;
                } else if (this.selectedIndex < 0) {
                    this.selectedIndex = total - 1;
                }
            },
            handleEnterKey() {
                if (this.showSuggestions && this.selectedIndex >= 0) {
                    this.selectSuggestion(this.selectedIndex);
                } else {
                    this.searchSubmit();
                }
            },
            selectSuggestion(itemOrIndex) {
                let text = '';
                let searchBy = 'keywords';

                if (typeof itemOrIndex === 'number') {
                    if (this.suggestions.length > 0 && this.suggestions[itemOrIndex]) {
                        text = this.suggestions[itemOrIndex].text;
                        searchBy = this.suggestions[itemOrIndex].searchBy || 'keywords';
                    } else if (this.lastKeywords.length > 0 && this.lastKeywords[itemOrIndex]) {
                        const key = this.lastKeywords[itemOrIndex];
                        text = this.tmpObj[key] ? this.tmpObj[key].text : '';
                        searchBy = this.tmpObj[key] ? this.tmpObj[key].searchBy : 'keywords';
                    }
                } else if (typeof itemOrIndex === 'object' && itemOrIndex !== null) {
                    text = itemOrIndex.text;
                    searchBy = itemOrIndex.searchBy || 'keywords';
                }

                if (text) {
                    this.keywords = text;
                    this.searchBy = searchBy;
                }
                this.showSuggestions = false;
                this.searchSubmit();
            },
            clearHistory() {
                try {
                    if (window.localStorage) {
                        window.localStorage.removeItem(searchHistoryStorageKey);
                    }
                } catch (e) {}
                this.tmpObj = {};
                this.suggestions = [];
                this.showSuggestions = false;
                this.historyVersion++;
            },
            searchOnClick(searchBy) {
                this.searchBy = searchBy;
                this.searchSubmit();
            },
            historySearchUrl(key) {
                const item = this.tmpObj[key] || {};
                return makeSearchUrl(item.searchBy, item.text);
            },
            resetSearchLoading() {
                if (this.searchNavigationTimer) {
                    clearTimeout(this.searchNavigationTimer);
                    this.searchNavigationTimer = null;
                }
                this.loading = false;
            },
            searchSubmit() {
                // Enter can trigger both keydown and submit. Only navigate once.
                if (this.loading) return;

                if (this.keywords !== '') this.saveKeyword();
                this.loading = true;
                const searchUrl = makeSearchUrl(this.searchBy, this.keywords);

                // A failed/cancelled navigation must not leave the button spinning forever.
                this.searchNavigationTimer = window.setTimeout(() => {
                    this.resetSearchLoading();
                }, 10000);

                try {
                    window.location.assign(searchUrl);
                } catch (error) {
                    this.resetSearchLoading();
                }
            },
            saveKeyword() {
                const keyword = normalizeSearchKeyword(this.keywords);
                if (!keyword) {
                    return;
                }

                let keywords = readSearchHistory();
                if (Object.prototype.hasOwnProperty.call(keywords, keyword)) {
                    keywords[keyword] = {
                        count: keywords[keyword].count + 1,
                        searchBy: this.searchBy,
                        time: Date.now()
                    };
                } else {
                    keywords[keyword] = {
                        count: 1,
                        searchBy: this.searchBy,
                        time: Date.now()
                    };
                }
                writeSearchHistory(keywords);
                this.historyVersion++;
            }
        }
    });

    showAdvancedApp.directive('click-outside', clickOutsideDirective);
    showAdvancedApp.mount('#search-wraper');
}

// Initialize slimsHomeApp Vue instance if element exists
if (document.getElementById('slims-home')) {
    const slimsHomeApp = Vue.createApp({});
    slimsHomeApp.directive('click-outside', clickOutsideDirective);
    slimsHomeApp.component('slims-subject', SlimsSubject);
    slimsHomeApp.component('slims-book', SlimsBook);
    slimsHomeApp.component('slims-member', SlimsMember);
    slimsHomeApp.component('slims-collection', SlimsCollection);
    slimsHomeApp.component('slims-group-subject', SlimsGroupSubject);
    slimsHomeApp.component('slims-group-member', SlimsGroupMember);
    slimsHomeApp.mount('#slims-home');
}

// Global Keyboard Shortcut (Ctrl+K / Cmd+K) for Search
(function () {
    const isMac = typeof navigator !== 'undefined' && /mac/i.test(navigator.platform || navigator.userAgent || '');
    
    document.addEventListener('DOMContentLoaded', function () {
        const kbdModifier = document.getElementById('search-kbd-modifier');
        if (kbdModifier) {
            kbdModifier.textContent = isMac ? '⌘' : 'Ctrl';
        }

        const searchBadge = document.getElementById('search-kbd-badge');
        if (searchBadge) {
            const focusSearch = function (event) {
                if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
                event.preventDefault();
                const searchInput = document.getElementById('search-input');
                if (searchInput) searchInput.focus();
            };
            searchBadge.addEventListener('click', focusSearch);
            searchBadge.addEventListener('keydown', focusSearch);
        }
    });

    document.addEventListener('keydown', function (e) {
        const isKbdShortcut = (e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K');
        if (isKbdShortcut) {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                e.preventDefault();

                if (document.activeElement === searchInput) {
                    searchInput.select();
                    return;
                }

                const searchWrapper = document.getElementById('search-wraper') || document.getElementById('search-form');
                if (searchWrapper) {
                    const rect = searchWrapper.getBoundingClientRect();
                    const isVisible = rect.top >= 0 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight);
                    if (!isVisible) {
                        searchWrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }

                searchInput.focus();
                if (searchInput.value) {
                    searchInput.select();
                }
            }
        }

        if (e.key === 'Escape') {
            const searchInput = document.getElementById('search-input');
            if (searchInput && document.activeElement === searchInput) {
                searchInput.blur();
            }
        }
    });
})();
