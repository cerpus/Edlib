const H5PEditor = window.H5PEditor = window.H5PEditor || {};
const ns = H5PEditor;

// Prevent library JS and CSS from being loaded
H5P.jsLoaded = H5P.cssLoaded = function (path) {
    return true;
}

ns.updateableFields = {};

/**
 * @typedef TranlationRefreshConfig
 * @type {Object}
 * @property {string} ajaxPath      Where libraries and translations can be loaded from
 * @property {string} library       Name of the main library/content type in format "machinename majorversion.minorversion" e.g. "H5P.FooBar 1.42"
 * @property {string} locale        Locale that the translations are in
*/

/**
 * Updates the translations in content semantics that are shown in the "Text overrides and translations"
 * section in the editor
 *
 * @param {TranlationRefreshConfig} config
 * @param {Object} parameters The content
 * @param {function} [updateStatus] Optional, function called to write to the log
 * @return {Promise<Object>}
 * @constructor
 */
ns.ContentTranslationRefresh = async function (config, parameters, updateStatus) {
    if (!config.library) {
        throw Error('Missing config setting "library"');
    }
    if (!config.locale) {
        throw Error('Missing config setting "locale"');
    }

    if (!ns.getAjaxUrl) {
        if (!config.ajaxPath) {
            throw Error('Missing config setting "ajaxPath"');
        }

        ns.getAjaxUrl = function (action, parameters) {
            let url = config.ajaxPath + action;
            const urLParams = new URLSearchParams();

            if (parameters !== undefined) {
                for (const property in parameters) {
                    if (parameters.hasOwnProperty(property)) {
                        urLParams.append(property, parameters[property]);
                    }
                }
                if (urLParams.size > 0) {
                    url += url.indexOf('?') === -1 ? '?' : '&';
                    url += urLParams.toString();
                }
            }

            return url;
        };
    }

    // Libraries required by content but not loaded
    ns.librariesToLoad = [];

    // Include translation when loading library
    ns.contentLanguage = ns.defaultLanguage = config.locale;

    // The main library a.k.a. the content type
    if (typeof ns.libraryCache[config.library] === "undefined") {
        ns.librariesToLoad.push(config.library);
    }

    // Check if other libraries are required
    getSubContentLibraries(parameters);

    if (ns.librariesToLoad.length > 0) {
        writeInfo('Loading libraries: ' + ns.librariesToLoad.join(', '));
    }

    const libs = await Promise.allSettled(
        ns.librariesToLoad.map(library => loadLibrary(library))
    ).then(results => {
        const success = [];
        const failed = [];

        results.forEach(result => {
            if (result.status === 'fulfilled') {
                success.push(result.value);
            } else if (result.status === 'rejected') {
                failed.push(result.reason);
            }
        });
        if (failed.length > 0) {
            throw Error('Failed to load libraries: ' + failed.join(', '));
        }

        return success;
    });

    if (await loadTranslations(config.locale)) {
        libs.forEach(({library, semantics}) => {
            if (ns.librariesToLoad.includes(library)) {
                ns.librariesToLoad.splice(ns.librariesToLoad.indexOf(library), 1);
            }
            if (!ns.renderableCommonFields[library] || !ns.renderableCommonFields[library].fields) {
                processSemanticsChunk(library, semantics);
            }
        });
        updateCommonFields(parameters, config.library);

        return parameters;
    }

    /**
     * Write a info message in the log
     *
     * @param {string} msg The message
     */
    function writeInfo(msg) {
        if (updateStatus) {
            updateStatus('    ' + msg);
        } else {
            console.log(msg);
        }
    }

    /**
     * Load library. Uses `ns.loadLibrary' in 'vendor/h5p/h5p-editor/js/h5peditor.js'
     *
     * @param {string} library The library to load in format "machinename majorversion.minorversion" e.g. "H5P.FooBar 1.42"
     * @return {Promise<Object>}
     */
    async function loadLibrary (library) {
        return new Promise((resolve, reject) => {
            if (ns.libraryCache[library] === 0) {
                reject(library);
            }
            // The loadLibrary() doesn't callback on error, so we give it 10s to reply
            const timeout = setTimeout(
                () => reject(library),
                10 * 1000
            );
            ns.loadLibrary(library, semantics => {
                clearTimeout(timeout);
                resolve({
                    library: library,
                    semantics: semantics,
                });
            });
        });
    }

    /**
     * Recursively traverse content and check if any additional libraries are used
     * Based on 'ns.Form.prototype.setSubContentDefaultLanguage' in 'vendor/h5p/h5p-editor/js/h5peditor-form.js'
     *
     * @param {Object|Array} parameters The content
     * @return {void}
     */
    function getSubContentLibraries (parameters) {
        if (!parameters) {
            return;
        }

        if (Array.isArray(parameters)) {
            for (let i = 0; i < parameters.length; i++) {
                getSubContentLibraries(parameters[i]);
            }
        } else if (typeof parameters === 'object') {
            if (parameters.library && !ns.libraryCache[parameters.library] && !ns.librariesToLoad.includes(parameters.library)) {
                ns.librariesToLoad.push(parameters.library);
            }

            for (const parameter of Object.keys(parameters)) {
                getSubContentLibraries(parameters[parameter]);
            }
        }
    }

    /**
     * Verify that we have the translation for the loaded libraries, if not request from server
     * Based on 'loadTranslations' in 'vendor/h5p/h5p-editor/js/h5peditor-form.js'
     *
     * @param {string} lang Lnaguage to load
     * @return {Promise<boolean>}
     */
    async function loadTranslations(lang) {
        return new Promise((resolve, reject) => {
            const loadLibs = [];

            for (let li in ns.libraryCache) {
                if (ns.libraryCache[li].translation[lang] === undefined) {
                    loadLibs.push(li);
                }
            }

            if (loadLibs.length) {
                writeInfo('Loading translations for ' + loadLibs.join(', '));
                $.ajax({
                    type: "POST",
                    url: ns.getAjaxUrl('translations', { language: lang }),
                    data: {
                        libraries: loadLibs,
                    },
                    dataType: 'json',
                    success: function (res) {
                        for (let lib in res.data) {
                            loadLibs.splice(loadLibs.indexOf(lib), 1);
                            ns.libraryCache[lib].translation[lang] = JSON.parse(res.data[lib]).semantics;
                        }
                        if (loadLibs.length === 0) {
                            resolve(true);
                        } else {
                            reject('Failed to load language for: ' + loadLibs.join(', '));
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                        reject('Failed loading translations: (' + jqXHR.status + ') ' + jqXHR?.responseJSON?.message);
                    },
                });
            } else {
                resolve(true);
            }
        });
    }

    /**
     * Recursive processing of the semantics for the libaries, only lookin for 'renderableCommonFields'
     * Updates `ns.rendereableCommonFields' with the fields found
     * Based on 'ns.processSemanticsChunk' in 'vendor/h5p/h5p-editor/js/h5peditor.js'
     *
     * @param {string} library Name of library that is being processed in format "machinename majorversion.minorversion" e.g. "H5P.FooBar 1.42"
     * @param {Object} semantics Semantics for the library
     * @returns void
     */
    function processSemanticsChunk (library, semantics) {
        for (let i = 0; i < semantics.length; i++) {
            const field = semantics[i];

            // Find common fields
            if (field.common !== undefined && field.common) {
                ns.renderableCommonFields[library] = ns.renderableCommonFields[library] || {};
                ns.renderableCommonFields[library].fields = ns.renderableCommonFields[library].fields || [];

                // Add renderable if it doesn't exist
                ns.renderableCommonFields[library].fields.push({
                    field: field,
                    parent: library,
                });
            } else if (field.type && field.type === "group") {
                processSemanticsChunk(library, field.fields);
            }
        }
    }

    /**
     * Recursive function that modifies 'parameters' and updates the common fields translations
     *
     * @param {Object} parameters The content
     * @param {string} library Name of the main library i.e. the content type in format "machinename majorversion.minorversion" e.g. "H5P.FooBar 1.42"
     * @return {void}
     */
    function updateCommonFields (parameters, library) {
        for (const propName of Object.keys(parameters)) {
            if (typeof parameters[propName] === "object" && parameters[propName].library) {
                updateCommonFields(parameters[propName], parameters[propName].library);
            } else if (ns.renderableCommonFields[library]) {
                ns.renderableCommonFields[library].fields.forEach(fields => {
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
            if (typeof parameters[propName] === 'object') {
                updateCommonFields(parameters[propName], library);
            }
        }
    }
}
