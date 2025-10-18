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
    });
})();
