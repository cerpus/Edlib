/**
 * Updates the translations in content semantics that are shown in the "Text overrides and translations"
 * section in the editor
 *
 * @param {string} library       Name of the main library/content type in format "machinename majorversion.minorversion" e.g. "H5P.FooBar 1.42"
 * @param {string} locale        Locale that the translations are in
 * @param {string} ajaxPath      Where libraries and translations can be loaded from
 * @param {function} [logFunction] Optional, function called to write to the log
 * @return {Promise<Object>}
 * @constructor
 */
ContentTranslationRefresh = function (library, locale, ajaxPath, logFunction) {
    if (!library) {
        throw Error('Missing config setting "library"');
    }
    if (!locale) {
        throw Error('Missing config setting "locale"');
    }
    if (!ajaxPath) {
        throw Error('Missing config setting "ajaxPath"');
    }

    this.library = library;
    this.locale = locale;
    this.ajaxPath = ajaxPath;
    this.logFunction = logFunction;

    this.librariesToLoad = [];
    this.renderableCommonFields = [];

    this.H5PEditorInit();

    // Main library/content type is not detected
    this.queueLibrary(library);
}

/**
 * H5PEditor settings
 */
ContentTranslationRefresh.prototype.H5PEditorInit = function () {
    if (!window.H5PEditor || !window.ns) {
        throw Error('H5PEditor not initialised/loaded');
    }

    // Prevent library JS and CSS from being loaded
    H5P.jsLoaded = H5P.cssLoaded = function (path) {
        return true;
    }

    ns.resetLoadedLibraries();

    // Translation to load with the library
    ns.contentLanguage = ns.defaultLanguage = this.locale;

    // Set ajaxPath used by H5PEditor.getAjaxUrl() in 'vendor/h5p/h5p-editor/scripts/h5peditor-init.js'
    if (!window.H5PIntegration) {
        window.H5PIntegration = {};
    }
    if (!window.H5PIntegration.editor) {
        window.H5PIntegration.editor = {};
    }
    if (!window.H5PIntegration.editor.ajaxPath) {
        window.H5PIntegration.editor.ajaxPath = this.ajaxPath;
    }
}

/**
 * Check if value is an Object
 *
 * @param {*} value
 * @return {boolean}
 */
ContentTranslationRefresh.prototype.isObject = value => value === Object(value);

/**
 * Add library to load queue if not already loaded or queued
 *
 * @param library
 */
ContentTranslationRefresh.prototype.queueLibrary = function (library) {
    if (ns.libraryCache && typeof ns.libraryCache[library] === 'undefined' && !this.librariesToLoad.includes(library)) {
        this.librariesToLoad.push(library);
    }
}

ContentTranslationRefresh.prototype.loadQueuedLibraries = async function () {
    const self = this;

    if (self.librariesToLoad.length > 0) {
        self.writeInfo('Loading libraries: ' + self.librariesToLoad.join(', '));
    }

    return Promise.allSettled(
        self.librariesToLoad.map(library => self.loadLibrary(library))
    ).then(results => {
        const success = [];
        const failed = [];

        results.forEach(result => {
            let library;
            if (result.status === 'fulfilled') {
                success.push(result.value);
                library = result.value.library;
            } else {
                console.log(result.reason);
                failed.push(result.reason.library);
                library = result.reason.library;
            }
            if (self.librariesToLoad.includes(library)) {
                self.librariesToLoad.splice(self.librariesToLoad.indexOf(library), 1);
            }
        });
        if (failed.length > 0) {
            throw Error('Failed to load libraries: ' + failed.join(', '));
        }

        return success;
    });
}

/**
 * Wrapper for `loadLibrary()' in 'vendor/h5p/h5p-editor/scripts/h5peditor.js'
 *
 * @param {string} library The library to load in format "machinename majorversion.minorversion" e.g. "H5P.FooBar 1.42"
 * @return {Promise<Object>}
 */
ContentTranslationRefresh.prototype.loadLibrary = async function (library) {
    const self = this;

    return new Promise((resolve, reject) => {
        // Library is queued by H5P, this will be the case if the library has previously failed to load
        if (ns.libraryCache[library] === 0) {
            reject({
                library: library,
                message: 'Already attempted',
            });
        }
        // loadLibrary() doesn't callback on error, so we give it 5 sec to reply
        const timeout = setTimeout(
            () => reject({
                library: library,
                message: 'Timeout'
            }),
            5 * 1000);
        H5PEditor.loadLibrary(library, semantics => {
            clearTimeout(timeout);
            resolve({
                library: library,
                semantics: semantics,
            });
        });
    });
}

/**
 * Process the content
 *
 * @param {Object} content The content
 * @return {Promise<Object>}
 */
ContentTranslationRefresh.prototype.process = async function (content) {
    if (!this.isObject(content)) {
        throw Error('Invalid content: Not an object');
    }

    // Check if other libraries are required
    this.getSubContentLibraries(content);

    const libs = await this.loadQueuedLibraries();
    const missing = this.translationsLoaded();
    if (missing.length > 0) {
        throw Error('Libraries missing translation: ' + missing.join(', '));
    }

    libs.forEach(({library, semantics}) => {
        if (!this.renderableCommonFields[library] || !this.renderableCommonFields[library].fields) {
            this.processSemanticsChunk(library, semantics);
        }
    });

    this.updateCommonFields(content, this.library);

    return content;
}

/**
 * Write info message to the log
 *
 * @param {string} msg The message
 */
ContentTranslationRefresh.prototype.writeInfo = function (msg) {
    if (this.logFunction) {
        this.logFunction('    ' + msg);
    } else {
        console.log(msg);
    }
}

/**
 * Recursively traverse content and check if any additional libraries are used
 * Based on 'H5PEditor.Form.prototype.setSubContentDefaultLanguage' in 'vendor/h5p/h5p-editor/scripts/h5peditor-form.js'
 *
 * @param {Object|Array} parameters The content
 * @return {void}
 */
ContentTranslationRefresh.prototype.getSubContentLibraries = function (parameters) {
    if (!parameters) {
        return;
    }

    if (Array.isArray(parameters)) {
        for (let i = 0; i < parameters.length; i++) {
            this.getSubContentLibraries(parameters[i]);
        }
    } else if (this.isObject(parameters)) {
        if (parameters.library) {
            this.queueLibrary(parameters.library);
        }

        for (const parameter of Object.keys(parameters)) {
            this.getSubContentLibraries(parameters[parameter]);
        }
    }
}

/**
 * Check that all libraries have translation for the requested language. Language should be included when the
 * library is loaded, but language may not be supported for sub content types/libraies.
 *
 * @return {Array}
 */
ContentTranslationRefresh.prototype.translationsLoaded = function () {
    const missing = [];

    for (let li in ns.libraryCache) {
        if (!ns.libraryCache[li].translation || !ns.libraryCache[li].translation[this.locale]) {
            missing.push(li);
        }
    }

    return missing;
}

/**
 * Recursive processing of the semantics for the libaries. Original function 'ns.processSemanticsChunk' in
 * 'vendor/h5p/h5p-editor/scripts/h5peditor.js' is not used as it attempts to render the fields, and we are only
 * interrested in the 'renderableCommonFields'.
 *
 * @param {string} library Name of library that is being processed in format "machinename majorversion.minorversion" e.g. "H5P.FooBar 1.42"
 * @param {Object} semantics Semantics for the library
 * @returns void
 */
ContentTranslationRefresh.prototype.processSemanticsChunk = function (library, semantics) {
    for (let i = 0; i < semantics.length; i++) {
        const field = semantics[i];

        // Find common fields
        if (field.common !== undefined && field.common) {
            this.renderableCommonFields[library] = this.renderableCommonFields[library] || {};
            this.renderableCommonFields[library].fields = this.renderableCommonFields[library].fields || [];

            // Add renderable if it doesn't exist
            this.renderableCommonFields[library].fields.push({
                field: field,
                parent: library,
            });
        } else if (field.type && field.type === "group") {
            this.processSemanticsChunk(library, field.fields);
        }
    }
}

/**
 * Recursive function that modifies content and updates the common fields translations
 *
 * @param {Object} parameters The content
 * @param {string} library Name of the main library i.e. the content type in format "machinename majorversion.minorversion" e.g. "H5P.FooBar 1.42"
 * @return {void}
 */
ContentTranslationRefresh.prototype.updateCommonFields = function (parameters, library) {
    for (const propName of Object.keys(parameters)) {
        if (this.isObject(parameters[propName]) && parameters[propName].library) {
            this.updateCommonFields(parameters[propName], parameters[propName].library);
        } else if (this.renderableCommonFields[library]) {
            this.renderableCommonFields[library].fields.forEach(fields => {
                if (fields.field.name && propName === fields.field.name) {
                    if (fields.field.fields) {
                        // it contains multiple fields
                        fields.field.fields.forEach(field => {
                            parameters[fields.field.name][field.name] = field.default;
                        });
                    } else {
                        // it's a single field
                        parameters[fields.field.name] = fields.field.default;
                    }
                }
            });
        }
        if (this.isObject(parameters[propName])) {
            this.updateCommonFields(parameters[propName], library);
        }
    }
}
