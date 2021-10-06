/**
 * Rate limiter functions
 *
 * @module ./library/RateLimiter
 */
class RateLimiter {
    static displayName = 'RateLimiter';

    /**
     * Limit the rate that 'func' can be called.
     * Instead of firing 'func' every time it is called, it will only be fired if not called in 'wait' milliseconds.
     *
     * @param {function} func       Function to call
     * @param {number} wait         Milliseconds to wait before calling func
     * @param {boolean} immediate   Trigger the function on the leading edge, instead of the trailing
     * @returns {Function}
     */
    static Debounce(func, wait, immediate) {
        let timeout;
        return (...args) => {
            const context = this;
            const later = () => {
                timeout = null;
                if (!immediate) {
                    func.apply(context, args);
                }
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait || 300);
            if (callNow) {
                func.apply(context, args);
            }
        };
    }
}

const set = (obj, path, val) => {
    const keys = path.split('.');
    const lastKey = keys.pop();
    const lastObj = keys.reduce((obj, key) =>
        obj[key] = obj[key] || {},
    obj);
    lastObj[lastKey] = val;
};

const nextTick = callback => {
    setTimeout(callback, 0);
};

const removeKeys = (obj, keys) => {
    if (Array.isArray(obj)) {
        return obj.map(o => removeKeys(o, keys));
    }

    if (typeof obj === 'object' && obj != null) {
        return Object.entries(obj).reduce((obj, [key, value]) => {
            if (keys.indexOf(key) === -1) {
                obj[key] = removeKeys(value, keys);
            }
            return obj;
        }, {});
    }

    return obj;
};

const compare = compareColumn => (a, b) => {
    if (a[compareColumn] < b[compareColumn]) {
        return -1;
    }
    if (a[compareColumn] > b[compareColumn]) {
        return 1;
    }
    return 0;
};

const Debounce = RateLimiter.Debounce;

const deepCopy = objectToCopy => JSON.parse(JSON.stringify(objectToCopy));

export { Debounce, set, nextTick, removeKeys, compare, deepCopy };

