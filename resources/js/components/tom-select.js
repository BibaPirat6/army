import TomSelect from 'tom-select';

export function initTomSelect(selector) {
    const element = document.querySelector(selector);

    if (!element) {
        return;
    }

    new TomSelect(element, {
        create: false,
        allowEmptyOption: true,
    });
}