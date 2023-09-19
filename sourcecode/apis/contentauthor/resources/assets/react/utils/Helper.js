import { iso6392 } from 'iso-639-2';

/**
 * Miscellaneous helper function
 *
 * @module ./library/Helper
 * @exports Helper
 */
class Helper {
    /**
     * Strip HTML tags
     *
     * @param {string} html
     * @param {bool} trimHtml
     * @returns {string}
     */
    static stripHTML(html, trimHtml) {
        if (html) {
            const elm = document.createElement('span');
            elm.innerHTML = html;
            return trimHtml !== false ? elm.textContent.trim() : elm.textContent;
        }

        return '';
    }

    /**
     * Validate an e-mail address
     *
     * @param {string} email The e-mail address to validate
     * @returns {boolean}
     */
    static EmailValidator(email) {
        const input = (email || '').toString().trim();

        if (input.length > 0) {
            const _emailValidationRegEx = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return _emailValidationRegEx.test(input);
        }

        return false;
    }

    /**
     * Validate multiple emails
     *
     * @param {array} emails
     * @returns {boolean}
     */
    static EmailsValidator(emails) {
        let valid = false;

        if (Array.isArray(emails) && emails.length > 0) {
            let allValid = true;

            emails.forEach(email => {
                allValid = (allValid && Helper.EmailValidator(email));
            });

            valid = allValid;
        }

        return valid;
    }

    static hasNestedProperty(...argms) {
        const args = Array.prototype.slice.call(argms, 1);
        let myObj = Object.assign({}, argms[0]);

        for (let i = 0; i < args.length; i++) {
            if (!myObj || !myObj.hasOwnProperty(args[i])) {
                return false;
            }
            myObj = myObj[args[i]];
        }

        return true;
    }

    /**
     * Compare two strings
     *
     * @param {string} a
     * @param {string} b
     * @returns {number}
     */
    static stringCompare(a, b) {
        if (a < b) {
            return -1;
        } else if (a > b) {
            return 1;
        }
        return 0;
    }

    static getLanguageStringFromCode(languageCode) {
        const languageElement = iso6392[iso6392.findIndex(language => language.iso6392B === languageCode)];

        if (languageElement) {
            let languageName = languageElement.name;
            if (languageElement.iso6392B === 'nno') {
                languageName = 'Nynorsk';
            } else if (languageElement.iso6392B === 'nob') {
                languageName = 'Bokmål';
            }
            return languageName;
        }
        return null;
    }
}

const stripHTML = Helper.stripHTML;
const EmailValidator = Helper.EmailValidator;
const EmailsValidator = Helper.EmailsValidator;
const hasNestedProperty = Helper.hasNestedProperty;
const stringCompare = Helper.stringCompare;
const getLanguageStringFromCode = Helper.getLanguageStringFromCode;

export { stripHTML, EmailValidator, EmailsValidator, hasNestedProperty, stringCompare, getLanguageStringFromCode };
