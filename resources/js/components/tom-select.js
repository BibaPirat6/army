import TomSelect from 'tom-select';

import 'tom-select/dist/css/tom-select.css';

export function initTomSelect(selector) {

    const element = document.querySelector(selector);

    if (!element) {
        return;
    }

    new TomSelect(
        element,
        {
            create: false,

            allowEmptyOption: true,

            placeholder: 'Выберите значение',

            maxOptions: 500,

            searchField: ['text'],

            sortField: {
                field: 'text',
                direction: 'asc',
            },
        }
    );
}