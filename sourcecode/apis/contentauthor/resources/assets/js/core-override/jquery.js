import jQuery from "jquery";

const H5P = window.H5P = window.H5P || {};
H5P.jQuery = jQuery;

const originalLoad = jQuery.fn.load;

// Copied from h5p-php-library
H5P.jQuery.fn.load = function (url, params, callback) {
    /**
     * NOTE:
     * This is needed in order to support old libraries that uses the .load() function
     * for elements in the deprecated jQuery way (elem.load(fn)), the correct way to do this
     * now is elem.on('load', fn)
     */
    if (typeof url === "function") {
        console.warn('You are using a deprecated H5P library. Please upgrade!');
        let args = Array.prototype.slice.call(arguments);
        args.unshift('load');
        return window.H5P.jQuery.fn.on.apply(this, args);
    }

    return originalLoad.apply(this, arguments);
}
