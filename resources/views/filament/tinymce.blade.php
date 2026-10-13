<script src="https://cdn.jsdelivr.net/npm/tinymce@8.8.2/tinymce.min.js"></script>
<script>
    (() => {
        const selector = 'textarea[data-tinymce="trustee-description"]';
        const initializeEditors = () => {
            if (!window.tinymce) return;
            document.querySelectorAll(selector).forEach((textarea) => {
                if (textarea.id && window.tinymce.get(textarea.id)) return;
                if (!textarea.id) textarea.id = `trustee-description-${Math.random().toString(36).slice(2)}`;
                window.tinymce.init({
                    target: textarea,
                    license_key: 'gpl',
                    menubar: false,
                    height: 420,
                    plugins: 'lists link',
                    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link | removeformat',
                    setup(editor) {
                        const sync = () => {
                            editor.save();
                            textarea.dispatchEvent(new Event('input', { bubbles: true }));
                            textarea.dispatchEvent(new Event('change', { bubbles: true }));
                        };
                        editor.on('change input undo redo keyup', sync);
                        editor.on('blur', sync);
                    },
                });
            });
        };
        initializeEditors();
        document.addEventListener('livewire:navigated', initializeEditors);
        new MutationObserver(initializeEditors).observe(document.body, { childList: true, subtree: true });
        document.addEventListener('submit', () => window.tinymce?.triggerSave(), true);
    })();
</script>
