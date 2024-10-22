import ChoicesJs from 'choices.js';

window.ChoicesJs = ChoicesJs;

/**
 * Replace the choices (options) for a Choices.js select
 *
 * @param {Element|null} elm
 * @param {{string: string} } options
 */
window.replaceChoicesJsOptions = function (elm, options) {
    if (elm && elm.choices) {
        const selected = elm.choices.getValue(true);
        const keys = Object.keys(options);
        const isMultiple = elm.getAttribute('multiple') !== null;
        const newOptions = [];

        keys.forEach((value) => {
            newOptions.push({
                'value': value,
                'label': options[value],
                'selected': selected.includes(value),
            });
        });

        // To update labels for selected items they have to be re-selected
        if (isMultiple) {
            elm.choices.removeActiveItems();
        }
        elm.choices.setChoices(newOptions, 'value', 'label', true);
    }
};
