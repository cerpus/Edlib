import { intlFormatDistance, isToday, isThisWeek } from "date-fns";

/**
 * Format date and/or time for time tags that have datetime attribute set. Also adds title attribute to the time tag
 * with date and time.
 *
 * Value of datetime attribute should be in ISO 8601 format, but anything that Date.parse() understands is valid.
 * If the value don't specify timezone, local timezone is assumed if it contains a time, UTC if not.
 * See https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/parse
 *
 * Locale is set with the lang attribute on the html tag, if not set the browser languages are used.
 *
 * The formats used for the title tag is 'full' datestyle and 'medium' time format.
 *
 * Options:
 *
 *    - Display human-readable difference to now. Both format types can be used, defaults are 'short' timestyle and
 *      'long' datestyle.
 *    - If today, text "today" and time. "today 12:07 PM"
 *    - If this week, the weekday and time. "Thursday 12:07 PM"
 *    - Otherwise only date is shown. "March 7, 2024"
 *    data-dh-relative = false {boolean}
 *
 *    - "Shortcut" formats
 *    data-dh-datestyle = 'short' {'full'|'long'|'medium'|'short'|'none'}
 *    data-dh-timestyle = 'medium' {'full'|'long'|'medium'|'short'|'none'}
 *
 *    - "Build your own" formats. Datestyle and timestyle are both ignored if present with any of these options.
 *    data-dh-weekday = undefined {'long', 'short', 'narrow'}
 *    data-dh-year = undefined {'numeric', '2-digit'}
 *    data-dh-month = undefined {'numeric', '2-digit', 'long', 'short', 'narrow'}
 *    data-dh-day = undefined {'numeric', '2-digit'}
 *    data-dh-hour = undefined {'numeric', '2-digit'}
 *    data-dh-minute = undefined {'numeric', '2-digit'}
 *    data-dh-second = undefined {'numeric', '2-digit'}
 *
 * See https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat/DateTimeFormat#date-time_component_options
 */
let locale = null;
const observeConfig = {
    subtree: true,
    childList: true,
    attributes: false,
};
const defaultDateStyle = 'short';
const defaultTimeStyle =  'medium';
const defaultRelativeTimeStyle = 'short';
const defaultRelativeDateStyle = 'long';
const timeParams = [
    'dayPeriod',
    'hour',
    'minute',
    'second',
];
const dateParams = [
    'weekday',
    'era',
    'year',
    'month',
    'day',
];

/**
 * Add observer so we get informed when page content changes
 *
 * @param {Element} target
 */
function observe(target) {
    if (!target) {
        throw new Error('Element is required')
    }
    const observer = new MutationObserver(callback);
    return observer.observe(target, observeConfig);
}

/**
 * Callback for observer
 *
 * @param {array<MutationRecord>} mutationList
 * @param {MutationObserver} observer
 */
function callback (mutationList, observer) {
    mutationList.forEach(record => {
        record.addedNodes.forEach(processContent);
    });
}

/**
 * @param {Element} el
 */
function processContent(el) {
    if (el.querySelectorAll) {
        el.querySelectorAll('time[datetime]').forEach(el => {
            const value = el.getAttribute('datetime');
            if (value) {
                const dhOptions = getOptions(el);
                const date = new Date(Date.parse(value));
                if (dhOptions.relative) {
                    el.innerText = relativeFormat(
                        date,
                        getIntlTimeOptions(el, defaultRelativeTimeStyle),
                        getIntlDateOptions(el, defaultRelativeDateStyle)
                    );
                } else {
                    el.innerText = format(date, getIntlOptions(el));
                }
                if (el.getAttribute('title') === null) {
                    el.setAttribute('title', format(date, {
                        dateStyle: 'full',
                        timeStyle: 'medium',
                    }));
                }
            }
        });
    }
}

/**
 * Format as date and time string
 *
 * @param {Date} value
 * @param {{}} options
 * @return {string}
 */
function format(value, options) {
    return new Intl.DateTimeFormat(locale, options).format(value);
}

/**
 * Difference in human-readable format
 *
 * @param {Date} value
 * @param {{}} timeOptions
 * @param {{}} dateOptions
 * @return {string}
 */
function relativeFormat(value, timeOptions, dateOptions) {
    const now = new Date(Date.now());
    let prefix = '';

    if (isToday(value)) {
        prefix = intlFormatDistance(value, now, {
            locale: locale,
            numeric: 'auto',
            style: 'long',
            unit: 'day',
        });
    } else if (isThisWeek(value)) {
        prefix = format(value, {weekday: 'long'});
    }

    return prefix ? `${prefix} ${format(value, timeOptions)}` : format(value, dateOptions);
}

/**
 * Get the Intl options set on the element
 *
 * @param {Element} el
 * @return {{}}
 */
function getIntlOptions(el) {
    let options = Object.assign({}, getIntlTimeOptions(el, defaultTimeStyle), getIntlDateOptions(el, defaultDateStyle));
    if ((options.timeStyle || options.dateStyle) &&
        (timeParams.some(item => options[item] !== undefined) || dateParams.some(item => options[item] !== undefined))
    ) {
        options.timeStyle = undefined;
        options.daStyle = undefined;
    }

    return options;
}

/**
 * Get the Intl time options set on the element
 *
 * @param {Element} el
 * @param {string} defaultStyle
 * @return {{}}
 */
function getIntlTimeOptions(el, defaultStyle) {
    let options = {};

    timeParams.forEach(param => options[param] = getStyleOption(el, `data-dh-${param.toLowerCase()}`));
    if (!timeParams.some(item => options[item] !== undefined)) {
        options.timeStyle = getStyleOption(el, 'data-dh-timestyle', defaultStyle);
    }

    return options;
}

/**
 * Get the Intl date options set on the element
 *
 * @param {Element} el
 * @param {string} defaultStyle
 * @return {{}}
 */
function getIntlDateOptions(el, defaultStyle) {
    let options = {};

    dateParams.forEach(param => options[param] = getStyleOption(el, `data-dh-${param.toLowerCase()}`));
    if (!dateParams.some(item => options[item] !== undefined)) {
        options.dateStyle = getStyleOption(el, 'data-dh-datestyle', defaultStyle);
    }

    return options;
}

/**
 * Get the DateHelper options set on the element
 *
 * @param {Element} el
 * @return {{relative: boolean}}
 */
function getOptions(el) {
    return {
        relative: el.getAttribute('data-dh-relative') === 'true',
    };
}

/**
 * Read date, time or short format options
 *
 * @param {Element} el
 * @param {string} name
 * @param {any} defaultValue
 * @return {string|undefined}
 */
function getStyleOption(el, name, defaultValue = undefined) {
    const value = el.getAttribute(name);
    return value !== 'none' ? (value ? value : defaultValue) : undefined;
}

document.addEventListener('DOMContentLoaded', (e) => {
    locale = document.documentElement.getAttribute('lang') ?? navigator.languages;
    processContent(document.body);
    observe(document.body);
});
