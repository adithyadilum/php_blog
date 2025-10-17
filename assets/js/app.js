(function () {
    const TOGGLE_URL = '/php_blog/api/toggle_like.php';
    const ACTIVE_CLASSES = ['bg-red-50', 'border-red-400', 'text-red-600'];
    const INACTIVE_CLASSES = ['bg-gray-100', 'border-gray-300', 'text-gray-700'];
    const INACTIVE_HOVER = ['hover:bg-red-50', 'hover:border-red-400'];

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

            // Ensure the underlying textarea stays in sync for form submissions
            editor.codemirror.on('change', () => {
                editor.codemirror.save();
            });

            // Populate initial value in case the textarea started with content
            editor.codemirror.save();
        });

        return true;
    }

    window.initMarkdownEditors = initMarkdownEditors;

    document.addEventListener('DOMContentLoaded', () => {
        const detailButton = document.querySelector('#like-btn[data-post]');
        if (detailButton) {
            const postId = detailButton.getAttribute('data-post');
            const labelEl = detailButton.querySelector('.like-label');
            const countElContainer = document.querySelector('#like-count span');

            detailButton.addEventListener('click', () => {
                withLoading(detailButton, () => sendToggleRequest(postId)
                    .then((data) => {
                        handleResponse(detailButton, data, {
                            countEl: countElContainer,
                            labelEl,
                        });
                    })
                    .catch((error) => {
                        console.error(error);
                        alert(error.message || 'Failed to update like');
                    }));
            });
        }

        if (!initMarkdownEditors()) {
            window.addEventListener('load', initMarkdownEditors, { once: true });
        }
    });
})();
