import './bootstrap';

import Alpine from 'alpinejs';
import $ from 'jquery';
import select2 from 'select2';
import 'select2/dist/css/select2.css';
import 'select2-tailwindcss-theme/dist/select2-tailwindcss-theme-plain.min.css';

window.Alpine = Alpine;
window.$ = $;
window.jQuery = $;

if (typeof select2 === 'function') {
    select2(window, $);
}

Alpine.start();

const initSelect2 = (root = document) => {
    const selects = root.querySelectorAll('select:not([data-native-select])');

    selects.forEach((select) => {
        if (select.classList.contains('select2-hidden-accessible')) {
            return;
        }

        const options = {
            theme: 'tailwindcss-3',
            width: '100%',
        };

        const emptyOption = Array.from(select.options).find((option) => option.value === '');
        if (emptyOption && !select.multiple) {
            options.placeholder = emptyOption.textContent?.trim() || '';
            options.allowClear = !select.required;
        }

        $(select).select2(options);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initSelect2();

    // Keep Select2 applied for selects inserted after initial page load.
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof Element)) {
                    return;
                }

                if (node.matches('select:not([data-native-select])')) {
                    initSelect2(node.parentElement || document);
                    return;
                }

                if (node.querySelector('select:not([data-native-select])')) {
                    initSelect2(node);
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
});

window.initSelect2 = initSelect2;
