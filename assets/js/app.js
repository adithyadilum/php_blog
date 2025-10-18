(function () {
    const TOGGLE_URL = '/php_blog/api/toggle_like.php';
    const ACTIVE_CLASSES = ['bg-charcoal', 'text-linen', 'border-charcoal'];
    const INACTIVE_CLASSES = ['bg-sand/60', 'text-charcoal', 'border-sand/80'];
    const INACTIVE_HOVER = ['hover:bg-charcoal', 'hover:text-linen'];

    function autoResizeTextarea(element) {
        if (!element) {
            return;
        }

        element.style.height = 'auto';
        element.style.height = `${element.scrollHeight}px`;
    }

    function initTextareaAutoresize() {
        document.querySelectorAll('textarea[data-autoresize]').forEach((textarea) => {
            if (textarea.dataset.autoresizeInitialized === 'true') {
                autoResizeTextarea(textarea);
                return;
            }

            textarea.dataset.autoresizeInitialized = 'true';
            autoResizeTextarea(textarea);
            textarea.addEventListener('input', () => autoResizeTextarea(textarea));
        });
    }

    function adjustMarkdownHeight(editor) {
        if (!editor || !editor.codemirror) {
            return;
        }

        const scroller = editor.codemirror.getScrollerElement();
        const wrapper = editor.codemirror.getWrapperElement();

        if (!scroller || !wrapper) {
            return;
        }

        scroller.style.height = 'auto';
        wrapper.style.height = 'auto';
        scroller.style.overflow = 'hidden';

        const newHeight = Math.max(scroller.scrollHeight, 240);
        scroller.style.height = `${newHeight}px`;
        wrapper.style.height = `${newHeight}px`;
    }

    function removeClasses(target, classes) {
        classes.forEach((name) => target.classList.remove(name));
    }

    function addClasses(target, classes) {
        classes.forEach((name) => target.classList.add(name));
    }

    function setButtonState(button, liked) {
        if (liked) {
            removeClasses(button, [...INACTIVE_CLASSES, ...INACTIVE_HOVER]);
            addClasses(button, ACTIVE_CLASSES);
        } else {
            removeClasses(button, ACTIVE_CLASSES);
            addClasses(button, [...INACTIVE_CLASSES, ...INACTIVE_HOVER]);
        }
    }

    function handleResponse(button, data, options = {}) {
        if (!data.success) {
            throw new Error(data.message || 'Failed to update like');
        }

        const liked = data.action === 'liked';
        setButtonState(button, liked);

        const { countEl, labelEl } = options;
        if (countEl && typeof data.count !== 'undefined') {
            countEl.textContent = data.count;
        }

        if (labelEl) {
            labelEl.textContent = liked ? 'Unlike' : 'Like';
        }
    }

    function sendToggleRequest(postId) {
        return fetch(TOGGLE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `post_id=${encodeURIComponent(postId)}`,
        }).then((response) => response.json());
    }

    function withLoading(button, callback) {
        button.disabled = true;
        button.classList.add('opacity-70');

        return Promise.resolve()
            .then(callback)
            .finally(() => {
                button.disabled = false;
                button.classList.remove('opacity-70');
            });
    }

    window.toggleLike = function toggleLike(postId) {
        const button = document.querySelector(`.like-btn[data-post-id="${postId}"]`);
        if (!button) {
            return;
        }

        const countEl = button.querySelector('.like-count');

        withLoading(button, () => sendToggleRequest(postId)
            .then((data) => {
                handleResponse(button, data, { countEl });
            })
            .catch((error) => {
                console.error(error);
                alert(error.message || 'Failed to update like');
            }));
    };

    function initMarkdownEditors() {
        if (!window.SimpleMDE) {
            return false;
        }

        document.querySelectorAll('[data-markdown-editor]').forEach((textarea) => {
            if (textarea.dataset.simplemdeInitialized === 'true') {
                return;
            }

            textarea.dataset.simplemdeInitialized = 'true';

            const editor = new window.SimpleMDE({
                element: textarea,
                autofocus: true,
                spellChecker: true,
                status: false,
                renderingConfig: {
                    singleLineBreaks: false,
                },
            });

            editor.codemirror.setOption('lineWrapping', true);
            editor.codemirror.refresh();
            adjustMarkdownHeight(editor);

            // Ensure the underlying textarea stays in sync for form submissions
            editor.codemirror.on('change', () => {
                editor.codemirror.save();
                adjustMarkdownHeight(editor);
            });

            editor.codemirror.on('refresh', () => adjustMarkdownHeight(editor));

            // Populate initial value in case the textarea started with content
            editor.codemirror.save();
        });

        initTextareaAutoresize();

        return true;
    }

    window.initMarkdownEditors = initMarkdownEditors;

    function shouldReduceMotion() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    let scrollAnimationFrame = null;

    function cancelScrollAnimation() {
        if (scrollAnimationFrame !== null) {
            window.cancelAnimationFrame(scrollAnimationFrame);
            scrollAnimationFrame = null;
            document.documentElement.style.scrollBehavior = '';
        }
    }

    function animateScrollTo(targetY) {
        if (shouldReduceMotion()) {
            window.scrollTo(0, targetY);
            return;
        }

        cancelScrollAnimation();

        const startY = window.pageYOffset;
        const distance = targetY - startY;

        if (Math.abs(distance) < 1) {
            return;
        }

        const duration = Math.min(900, Math.max(500, Math.abs(distance) * 0.7));
        const direction = distance >= 0 ? 1 : -1;
        const overshootBase = Math.abs(distance) * 0.085;
        const overshoot = Math.min(64, Math.max(14, overshootBase));
        const overshootTarget = targetY + direction * overshoot;

        document.documentElement.style.scrollBehavior = 'auto';

        const easeOutBack = (t) => {
            const c1 = 0.85;
            const c3 = c1 + 1;
            const v = t - 1;
            return 1 + c3 * v * v * v + c1 * v * v;
        };

        let startTime = null;

        const step = (timestamp) => {
            if (startTime === null) {
                startTime = timestamp;
            }

            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = easeOutBack(progress);
            const currentY = startY + (overshootTarget - startY) * eased;

            window.scrollTo(0, currentY);

            if (progress < 1) {
                scrollAnimationFrame = window.requestAnimationFrame(step);
            } else {
                document.documentElement.style.scrollBehavior = '';
                window.scrollTo(0, targetY);
                scrollAnimationFrame = null;
            }
        };

        scrollAnimationFrame = window.requestAnimationFrame(step);
    }

    function initSmoothAnchors() {
        const links = document.querySelectorAll('a[href*="#"]:not([href="#"]):not([href="#0"])');
        if (!links.length) {
            return;
        }

        links.forEach((link) => {
            link.addEventListener('click', (event) => {
                const href = link.getAttribute('href');
                if (!href) {
                    return;
                }

                let url;
                try {
                    url = new URL(href, window.location.href);
                } catch (error) {
                    return;
                }

                if (url.origin !== window.location.origin) {
                    return;
                }

                const normalizePath = (path) => path.replace(/\/index\.php$/i, '/');
                const isSamePath = normalizePath(url.pathname) === normalizePath(window.location.pathname);
                if (!isSamePath) {
                    return;
                }

                const targetId = url.hash.replace('#', '');
                if (!targetId) {
                    return;
                }

                const target = document.getElementById(targetId);

                if (!target) {
                    return;
                }

                event.preventDefault();

                const header = document.querySelector('header');
                const headerOffset = header ? header.offsetHeight : 0;
                const rect = target.getBoundingClientRect();
                const targetY = rect.top + window.pageYOffset - Math.max(headerOffset - 24, 0);

                animateScrollTo(Math.max(targetY, 0));
            });
        });
    }

    function initCardReveal() {
        const cards = document.querySelectorAll('.story-card');
        if (!cards.length) {
            return;
        }

        if (shouldReduceMotion() || !('IntersectionObserver' in window)) {
            cards.forEach((card) => {
                card.classList.add('is-visible');
                card.style.transitionDelay = '';
                card.removeAttribute('data-transition-delay');
            });
            return;
        }

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    const delayValue = entry.target.dataset.transitionDelay;
                    if (delayValue) {
                        window.setTimeout(() => {
                            entry.target.style.transitionDelay = '';
                            entry.target.removeAttribute('data-transition-delay');
                        }, 700);
                    }
                    obs.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.25,
            rootMargin: '0px 0px -10% 0px',
        });
        cards.forEach((card, index) => {
            const delay = Math.min(index * 0.08, 0.4);
            card.dataset.transitionDelay = `${delay}s`;
            card.style.transitionDelay = `${delay}s`;
            observer.observe(card);
        });

        window.setTimeout(() => {
            cards.forEach((card) => {
                const rect = card.getBoundingClientRect();
                if (rect.top <= window.innerHeight * 0.95) {
                    card.classList.add('is-visible');
                    observer.unobserve(card);
                    window.setTimeout(() => {
                        card.style.transitionDelay = '';
                        card.removeAttribute('data-transition-delay');
                    }, 700);
                }
            });
        }, 80);
    }

    document.addEventListener('DOMContentLoaded', () => {
        initTextareaAutoresize();

        if (!initMarkdownEditors()) {
            window.addEventListener('load', initMarkdownEditors, { once: true });
        }

        const menuToggle = document.querySelector('[data-nav-menu-toggle]');
        const mobileMenu = document.querySelector('[data-mobile-menu]');
        const searchToggle = document.querySelector('[data-nav-search-toggle]');
        const mobileSearch = document.querySelector('[data-mobile-search]');
        const mobileSearchInput = mobileSearch ? mobileSearch.querySelector('input') : null;
        const mqDesktop = window.matchMedia('(min-width: 768px)');

        function closeElement(el, toggle) {
            if (!el) {
                return;
            }
            el.classList.add('hidden');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        }

        function openElement(el, toggle) {
            if (!el) {
                return;
            }
            el.classList.remove('hidden');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'true');
            }
        }

        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', () => {
                const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
                if (isExpanded) {
                    closeElement(mobileMenu, menuToggle);
                } else {
                    openElement(mobileMenu, menuToggle);
                    closeElement(mobileSearch, searchToggle);
                }
            });
        }

        if (searchToggle && mobileSearch) {
            searchToggle.addEventListener('click', () => {
                const isExpanded = searchToggle.getAttribute('aria-expanded') === 'true';
                if (isExpanded) {
                    closeElement(mobileSearch, searchToggle);
                } else {
                    openElement(mobileSearch, searchToggle);
                    closeElement(mobileMenu, menuToggle);
                    window.setTimeout(() => {
                        if (mobileSearchInput) {
                            mobileSearchInput.focus();
                        }
                    }, 50);
                }
            });
        }

        function handleBreakpointChange(event) {
            if (event.matches) {
                closeElement(mobileMenu, menuToggle);
                closeElement(mobileSearch, searchToggle);
            }
        }

        if (mqDesktop && typeof mqDesktop.addEventListener === 'function') {
            mqDesktop.addEventListener('change', handleBreakpointChange);
        } else if (mqDesktop && typeof mqDesktop.addListener === 'function') {
            mqDesktop.addListener(handleBreakpointChange);
        }

        initSmoothAnchors();

        if (!shouldReduceMotion()) {
            document.body.classList.add('card-animate');
        }

        initCardReveal();
        window.addEventListener('wheel', cancelScrollAnimation, { passive: true });
        window.addEventListener('touchmove', cancelScrollAnimation, { passive: true });
        window.addEventListener('keydown', cancelScrollAnimation);

        (function initToasts() {
            const toastNodes = document.querySelectorAll('[data-toast]');
            if (!toastNodes.length) {
                return;
            }

            const stackId = 'toast-stack';
            let stack = document.getElementById(stackId);
            if (!stack) {
                stack = document.createElement('div');
                stack.id = stackId;
                stack.className = 'toast-stack';
                document.body.appendChild(stack);
            }

            const defaultDismiss = 4200;
            const staggerDelay = 120;

            toastNodes.forEach((toast, index) => {
                if (toast.parentElement !== stack) {
                    stack.appendChild(toast);
                }

                ['mb-4', 'mx-auto', 'max-w-md', 'pointer-events-auto'].forEach((cls) => toast.classList.remove(cls));
                toast.setAttribute('aria-live', toast.getAttribute('role') === 'alert' ? 'assertive' : 'polite');

                window.setTimeout(() => {
                    toast.classList.add('show');
                }, staggerDelay * index);

                const duration = Number(toast.dataset.toastDuration || defaultDismiss);

                const timeoutId = window.setTimeout(() => {
                    toast.classList.remove('show');
                    window.setTimeout(() => {
                        toast.remove();
                    }, 380);
                }, duration + staggerDelay * index);

                toast.dataset.toastTimeout = timeoutId;

                toast.addEventListener('click', () => {
                    toast.classList.remove('show');
                    window.setTimeout(() => toast.remove(), 220);
                    const activeTimeout = toast.dataset.toastTimeout;
                    if (activeTimeout) {
                        window.clearTimeout(Number(activeTimeout));
                    }
                });
            });
        })();
    });
})();
