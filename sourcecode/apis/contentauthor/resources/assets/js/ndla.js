import { initAudioBrowser, initImageBrowser, initVideoBrowser } from '../react/contentBrowser';
import { getLicenseByNBTitle } from '@ndla/licenses';

const $ = window.H5P.jQuery;

/**
 * Content browser editor widget module.
 */

class ContentBrowserBase {
    /**
     * Providers incase mime type is unknown.
     * @public
     */
    providers = [
        {
            name: 'Brightcove',
            regexp: /https:\/\/bc\/(?:ref:[a-z0-9]+|\d+)/i,
            aspectRatio: '16:9'
        }
    ];

    $item = null;

    constructor(parent, field, params) {
        this.field = field;
        this.parent = parent;
        this.params = params;
        this.cbContent = false;
        this.field.enableCustomQualityLabel = false;

        this.toggleContentBrowser = null;
        this.copyrightHandler = field.disableCopyright ? new Metadata(this) : new Copyright(this);
    }

    /**
     * @abstract
     */
    getApiBaseUrl() {
        throw new Error('not implemented');
    }

    /**
     * @abstract
     */
    getExternalId() {
        throw new Error('not implemented');
    }

    /**
     * @abstract
     */
    setCopyright(values) {
        throw new Error('not implemented for this content type');
    }

    /**
     * @return {Promise}
     */
    syncLicense() {
        return new Promise((resolve, reject) => {
            if (this.cbContent) {
                return $.get(this.getApiBaseUrl() + '/' + this.getExternalId())
                    .done((data) => resolve(data))
                    .fail(reject);
            }

            resolve();
        }).then(values => values && this.setCopyright(values));
    }

    setValue(setValue) {
        return (field, params) => {
            if (this.cbContent) {
                this.copyrightHandler.reset();
                this.cbContent = false;
            }

            this.params = this.widget.params = params;
            setValue(field, params);
        };
    }

    /**
     * Update field at the given path with the given value.
     *
     * @param {String} path
     * @param {String} value
     * @param {Boolean} disable
     * @returns {undefined}
     */
    setField(path, value, disable) {
        disable = disable || false;

        const field = H5PEditor.findField(path, this.widget);
        if (field !== false) {
            if (field instanceof H5PEditor.Text && (H5P.trim(field.$input.val()) === '' || disable)) {
                if (value !== undefined) {
                    field.$input.val(value).change();
                    field.$errors.html('');
                }
                field.$input.attr('disabled', disable);

            } else if (field instanceof H5PEditor.Select) {
                if (value !== undefined) {
                    field.$select.val(value).change();
                }
                field.$select.attr('disabled', disable);
            }
        }
    };

    /**
     * Opens content searcher.
     *
     * @returns {undefined}
     */
    open() {
        this.toggleContentBrowser();
    };

    /**
     * Always validate.
     *
     * @returns {Boolean} true
     */
    validate() {
        return true;
    };

    /**
     * Remove field.
     *
     * @returns {undefined}
     */
    remove() {
        this.widget.remove();
    };

    init() {
    }

    appendTo($wrapper) {
        var that = this;

        const $dummy = $('<div></div>');
        this.widget.appendTo($dummy);
        this.$item = this.widget.$item;
        this.$file = $dummy.find('.file');
        this.$file.find('.add').unbind('click').click(function () {
            that.toggleContentBrowser();
            return false;
        });
        this.cbContainer = $('<div id="cbContainer"></div>').appendTo(this.$file);

        this.init();

        $dummy.children().appendTo($wrapper);

        if (this.params !== undefined) {
            if (this.params.externalId !== undefined || (this.params[0] !== undefined && this.params[0].externalId !== undefined)) {
                this.parent.ready(function () {
                    that.setField('copyright/author', undefined, true);
                    that.setField('copyright/source', undefined, true);
                    that.setField('copyright/license', undefined, true);
                    that.setField('copyright/version', undefined, true);
                });
            }
        }
    }

    useUrl(original) {
        return url => {
            this.widget.updateIndex = 0;
            this.widget.$files.children().each(child => $(child).remove());
            original.call(this.widget, url);
            this.setValue(this.field, this.params);
        };
    }

    /**
     * Check if a object has the given properties.
     *
     * @static
     * @param {object} content
     * @param {string|[]} requirements
     * @returns {boolean}
     */
    static checkNestedRequirements(content, requirements) {
        if (typeof content === 'undefined') {
            return false;
        }
        if (typeof requirements === 'string') {
            requirements = requirements.split('.');
        }
        for (let i = 1; i < requirements.length; i++) {
            if (content === null || !content.hasOwnProperty(requirements[i])) {
                return false;
            }
            content = content[requirements[i]];
        }
        return true;
    };

    /**
     * Get localized strings.
     *
     * @param {String} key
     * @param {Object} params
     * @returns {@exp;H5PEditor@call;t}
     */
    static t(key, params) {
        return H5PEditor.t('core', key, params);
    };
}

class AudioBrowser extends ContentBrowserBase {
    constructor(parent, field, params, setValue) {
        super(parent, field, params);
        this.cbContent = params && params[0] && params[0].externalId;
        this.widget = new window.originalAudioWidget(parent, field, this.params, this.setValue(setValue));
        originalAudioWidget.providers = originalAudioWidget.providers.concat(this.providers);
        this.changes = this.widget.changes;
    }

    appendTo($wrapper) {
        super.appendTo($wrapper);
        const brightcoveContainer = $('<div class="h5p-dialog-box brightcove"><h3>NDLA API</h3><button>' + ContentBrowserBase.t('addEntity', { ':entity': 'audio' }) + '</button></div>');
        brightcoveContainer.find('button').click(() => {
            this.open();
            return false;
        });
        this.widget.$addDialog.find('.h5p-dialog-box').first().replaceWith(brightcoveContainer);
        this.widget.$add.toggleClass('hidden', this.widget.params !== undefined && this.widget.params.length > 0);
        this.widget.useUrl = this.useUrl(this.widget.useUrl);
        this.widget.openFileSelector = () => {
            this.open();
            return false;
        };
    }

    getApiBaseUrl() {
        return '/audios/browse';
    }

    getExternalId() {
        return this.params[0].externalId;
    }

    buildCopyright(values) {
        const copyRight = new CopyrightDataObject();
        copyRight.title = ContentBrowserBase.checkNestedRequirements(values, 'values.title.title') ? values.title.title.trim() : '';
        if (ContentBrowserBase.checkNestedRequirements(values, 'values.copyright.license.license') && values.copyright.license.license.toLowerCase() !== 'unknown') {
            const licenseFragments = values.copyright.license.license.split('-');
            const licenseVersion = licenseFragments.pop();
            if (licenseFragments.length > 0) {
                if (licenseFragments[0] === 'CC') {
                    const licenseFormatted = [licenseFragments.shift()];
                    licenseFormatted.push(licenseFragments.join('-'));
                    copyRight.license = licenseFormatted.join(' ');
                } else {
                    copyRight.license = licenseFragments.join('-');
                }
                if (licenseFragments.length > 0 && licenseVersion > 0) {
                    copyRight.version = licenseVersion;
                }
            } else {
                copyRight.license = values.copyright.license.license;
            }
        }

        let authors = [];
        if (ContentBrowserBase.checkNestedRequirements(values, 'values.copyright.creators')) {
            authors = authors.concat(values.copyright.creators);
        }
        if (ContentBrowserBase.checkNestedRequirements(values, 'values.copyright.rightsholders')) {
            authors = authors.concat(values.copyright.rightsholders);
        }

        copyRight.authors = authors;
        return copyRight;
    }

    setCopyright(values) {
        if (!ContentBrowserBase.checkNestedRequirements(values, 'values.audioFile.url')) {
            throw new Error('Missing url');
        }

        this.widget.useUrl(values.audioFile.url);
        this.params[0].externalId = values.id;
        this.copyrightHandler.set(this.buildCopyright(values), this.params);
        if (this.params.copyright) {
            this.widget.params.copyright = this.params.copyright;
        }
    }

    init() {
        const promise = this.syncLicense();

        initAudioBrowser(this.cbContainer.get(0), {
            onSelectCallback: values => {
                promise.finally(() => {
                    this.setCopyright(values);
                    this.toggleContentBrowser();
                    this.widget.$add.toggleClass('hidden', this.widget.params.length > 0);
                    this.widget.$addDialog.removeClass('h5p-open');
                });
            },
            onToggleCallback: cb => this.toggleContentBrowser = cb,
            locale: H5PIntegration.locale,
            getCurrentLanguage: () => H5PEditor.defaultLanguage,
            searchUrl: H5PIntegration.audioBrowserConfig.searchUrl,
            detailsUrl: H5PIntegration.audioBrowserConfig.detailsUrl,
            searchParams: H5PIntegration.audioBrowserConfig.searchParams,
        });
    }
}

class ImageBrowser extends ContentBrowserBase {
    constructor(parent, field, params, setValue) {
        super(parent, field, params);
        this.cbContent = params && params.externalId;
        this.widget = new window.originalImageWidget(parent, field, params, this.setValue(setValue));
        this.changes = this.widget.changes;
    }

    appendTo($wrapper) {
        super.appendTo($wrapper);
        const widget = this.widget;
        this.widget.openFileSelector = () => {
            this.open();
            return false;
        };

        this.widget.editImagePopup.on('imageSaved', event => {
            widget.isEditing = false;
            widget.isOriginalImage = false;

            if (!widget.params.originalImage) {
                widget.params.originalImage = {
                    path: widget.params.path,
                    mime: widget.params.mime,
                    height: widget.params.height,
                    width: widget.params.width
                };
            }
            this.addParams(widget, event.data);
            this.copyrightHandler.set(this.copyrightHandler.get(), widget.params);
        });

    }

    addParams(widget, params) {
        widget.params = params;
        widget.setValue(widget.field, params);
        for (let i = 0; i < widget.changes.length; i++) {
            widget.changes[i](params);
        }
        widget.addFile();
    }

    buildCopyright(values) {
        const copyRight = new CopyrightDataObject();
        let authors = [];

        if (ContentBrowserBase.checkNestedRequirements(values, 'values.title.title')) {
            copyRight.title = values.title.title.trim();
        }
        if (ContentBrowserBase.checkNestedRequirements(values, 'values.copyright.creators')) {
            authors = authors.concat(values.copyright.creators);
        }
        if (ContentBrowserBase.checkNestedRequirements(values, 'values.copyright.rightsholders')) {
            authors = authors.concat(values.copyright.rightsholders);
        }

        if (authors.length > 0) {
            copyRight.authors = authors;
        }

        if (ContentBrowserBase.checkNestedRequirements(values, 'values.copyright.origin')) {
            copyRight.source = values.copyright.origin;
        }
        if (ContentBrowserBase.checkNestedRequirements(values, 'values.copyright.license.license') && values.copyright.license.license.toLowerCase() !== 'unknown') {
            const licenseFragments = values.copyright.license.license.split('-');
            let licenseVersion = licenseFragments.pop();
            const H5PLicenses = H5P.copyrightLicenses;
            if (licenseFragments.length > 0) {
                if (licenseFragments[0] === 'CC0') {
                    copyRight.license = 'CC0 1.0';
                    licenseVersion = null;
                } else if (licenseFragments[0] === 'CC') {
                    const licenseFormatted = [licenseFragments.shift()];
                    licenseFormatted.push(licenseFragments.join('-'));
                    copyRight.license = licenseFormatted.join(' ');
                } else {
                    copyRight.license = licenseFragments.join('-');
                }
                if (licenseFragments.length > 0 && licenseVersion > 0) {
                    copyRight.version = licenseVersion;
                }
            } else if (values.copyright.license.license === 'COPYRIGHTED') {
                copyRight.license = 'C';
            } else {
                copyRight.license = values.copyright.license.license;
            }

            if (!H5PLicenses[copyRight.license]) {
                copyRight.license = null;
                copyRight.version = null;
            }
        }
        return copyRight;
    }

    buildParams(values) {
        const params = {
            path: values.image.imageUrl,
            mime: values.image.contentType,
            externalId: values.id,
            metadataUrl: values.metaUrl
        };

        if (ContentBrowserBase.checkNestedRequirements(values, 'values.alttext.alttext')) {
            params.alt = values.alttext.alttext;
            this.setField('../alt', values.alttext.alttext, false);
        }

        if (ContentBrowserBase.checkNestedRequirements(values, 'values.title.title')) {
            params.title = values.title.title.trim();
        }

        return params;
    }

    getApiBaseUrl() {
        return '/images/browse';
    }

    getExternalId() {
        return this.params.externalId;
    }

    setCopyright(values) {
        const params = this.buildParams(values);
        this.copyrightHandler.set(this.buildCopyright(values), params);
        if (params.copyright) {
            this.params.copyright = this.widget.params.copyright = params.copyright;
        }
    }

    init() {
        const promise = this.syncLicense();

        initImageBrowser(this.cbContainer.get(0), {
            onSelectCallback: values => {
                promise.finally(() => {
                    this.copyrightHandler.reset();

                    const params = this.buildParams(values);

                    const image = new Image();
                    const that = this;
                    image.onload = function () { //don't make this into an arrow function. Need "this" to point to the image, not the content browser
                        params.width = this.width;
                        params.height = this.height;
                        that.addParams(that.widget, params);
                        that.setCopyright(values);
                        that.toggleContentBrowser();
                    };
                    image.src = params.path;
                });
            },
            onToggleCallback: cb => this.toggleContentBrowser = cb,
            locale: H5PIntegration.locale,
            getCurrentLanguage: () => H5PEditor.defaultLanguage,
            searchUrl: H5PIntegration.imageBrowserConfig.searchUrl,
            detailsUrl: H5PIntegration.imageBrowserConfig.detailsUrl,
            searchParams: H5PIntegration.imageBrowserConfig.searchParams,
        });
        this.copyrightHandler.handleDisplayCopyrightButton();
    }
}

class VideoBrowser extends ContentBrowserBase {
    constructor(parent, field, params, setValue) {
        super(parent, field, params);
        this.cbContent = params && params[0] && params[0].mime === 'video/Brightcove';
        this.widget = new window.originalVideoWidget(parent, field, params, this.setValue(setValue));
        originalVideoWidget.providers = originalVideoWidget.providers.concat(this.providers);
        this.changes = this.widget.changes;
    }

    appendTo($wrapper) {
        super.appendTo($wrapper);
        const brightcoveContainer = $('<div class="h5p-dialog-box brightcove"><h3>Brightcove</h3><button>' + ContentBrowserBase.t('addEntity', { ':entity': 'video' }) + '</button></div>');
        brightcoveContainer.find('button').click(() => {
            this.toggleContentBrowser();
            return false;
        });
        this.widget.$addDialog.find('.h5p-dialog-box').first().replaceWith(brightcoveContainer);
        this.widget.$add.toggleClass('hidden', this.widget.params !== undefined && this.widget.params.length > 0);
        this.widget.useUrl = this.useUrl(this.widget.useUrl);
        // Disable YouTube in H5P for NDLA #618
        if (window.isNotAdmin) {
            const input = document.querySelector('.video .h5p-file-url.h5peditor-text');
            if (input) {
                input.setAttribute("disabled", '');
            }
        }
    }

    getApiBaseUrl() {
        return '/videos/browse';
    }

    getExternalId() {
        return this.params[0].path.replace(/^.*\//, '');
    }

    setCopyright(values) {
        this.copyrightHandler.set(this.buildCopyright(values));
        this.setTextTracks(values);
    }

    setTextTracks(values) {
        const field = H5PEditor.findField('textTracks', this.parent) || H5PEditor.findField('a11y', this.parent);
        if (field) {
            const trackField = field.children[0];
            (trackField.getValue() || [])
                .forEach(() => {
                    trackField.removeItem(0)
                });
            for (let track of values.text_tracks) {
                const trackData = {
                    kind: track.kind,
                    label: track.label,
                    srcLang: track.srclang.split('-')[0],
                    track: {
                        externalId: track.id,
                        path: `https://bc?id=${encodeURIComponent(values.id)}&track=${encodeURIComponent(track.id)}`,
                        mime: track.mime_type,
                    }
                };
                trackField.addItem(trackData);
            }
            trackField.widget.removeButton();
        }
    }

    buildCopyright(values) {
        const copyRight = new CopyrightDataObject();
        copyRight.title = ContentBrowserBase.checkNestedRequirements(values, 'values.name') ? values.name.trim() : '';

        if (ContentBrowserBase.checkNestedRequirements(values, 'values.licenseInfo.abbreviation')) {
            copyRight.license = values.licenseInfo.abbreviation;
        } else if (ContentBrowserBase.checkNestedRequirements(values, 'values.custom_fields.license')) {
            const licenseData = getLicenseByNBTitle(values.custom_fields.license);
            if (licenseData && licenseData.abbreviation) {
                copyRight.license = licenseData.abbreviation;
            }
        }

        if (ContentBrowserBase.checkNestedRequirements(values, 'values.custom_fields.licenseinfo')) {
            copyRight.authors.push({ name: values.custom_fields.licenseinfo });
        }
        if (ContentBrowserBase.checkNestedRequirements(values, 'values.custom_fields.licenseinfo2')) {
            copyRight.authors.push({ name: values.custom_fields.licenseinfo2 });
        }
        return copyRight;
    }

    init() {
        const promise = this.syncLicense();

        initVideoBrowser(this.cbContainer.get(0), {
            onSelectCallback: values => {
                promise.finally(() => {
                    this.copyrightHandler.reset();
                    const path = values.projection === 'equirectangular' ? 360 : 0;
                    this.widget.useUrl(`https://bc/${path}/${values.id}`);
                    this.setCopyright(values);
                    this.toggleContentBrowser();
                    this.widget.$add.toggleClass('hidden', this.widget.params.length > 0);
                    this.widget.$addDialog.removeClass('h5p-open');
                });
            },
            onToggleCallback: cb => this.toggleContentBrowser = cb,
            locale: H5PIntegration.locale,
        });
    }
}

class Copyright {

    copyright = new CopyrightDataObject();

    constructor(contentBrowser) {
        this.contentBrowser = contentBrowser;
    }

    reset() {
        const {
            contentBrowser
        } = this;
        if (contentBrowser.field.type === 'image') {
            contentBrowser.setField('../alt', '', false);
        }
        contentBrowser.setField('copyright/title', '', false);
        contentBrowser.setField('copyright/author', '', false);
        contentBrowser.setField('copyright/year', '', false);
        contentBrowser.setField('copyright/source', '', false);
        contentBrowser.setField('copyright/version', '', false);
        contentBrowser.setField('copyright/license', 'U', false);
    }

    get() {
        return this.copyright;
    }

    set(copyrightValues, params) {
        let values = {
            title: copyrightValues.title,
            license: copyrightValues.license,
            version: copyrightValues.version,
            source: copyrightValues.source,
        };

        let year = '';
        if (copyrightValues.yearFrom !== null) {
            year = copyrightValues.yearFrom;
        }
        if (copyrightValues.yearTo !== null) {
            year += ' - ' + copyrightValues.yearTo;
        }

        if (year.length > 0) {
            values.year = year;
        }

        if (copyrightValues.authors.length > 0) {
            values.author = copyrightValues.authors
                .map(author => author.name)
                .reduce((accumulator, current) => {
                    if (accumulator.indexOf(current) === -1) {
                        accumulator.push(current);
                    }
                    return accumulator;
                }, [])
                .join(', ');
        }

        if (values.license === 'CC0 1.0') {
            values.version = values.license;
            values.license = 'PD';
        }

        Object.keys(values)
            .filter(index => values[index] !== null)
            .forEach((index) => this.contentBrowser.setField('copyright/' + index, values[index], true));

        if (params) {
            params.copyright = values;
        }

        this.copyright = copyrightValues;
    }

    handleDisplayCopyrightButton() {
    }
}

class Metadata {

    metadata = new CopyrightDataObject();

    constructor(contentBrowser) {
        this.contentBrowser = contentBrowser;
    }

    reset() {
        this.contentBrowser.parent.metadataForm.resetMetadata();
    }

    get() {
        return this.metadata;
    }

    set(copyrightValues) {
        let metadataValues = {};

        Object.keys(copyrightValues)
            .filter(index => copyrightValues[index] !== null)
            .forEach(index => {
                const value = copyrightValues[index];
                if (index !== 'authors') {
                    metadataValues[index] = H5PEditor.MetadataForm.createMetadataObject({
                        value: value,
                        readonly: true,
                    });
                }
            });

        if (copyrightValues.authors.length > 0) {
            const roles = ['Author', 'Editor', 'Licensee', 'Originator'];
            metadataValues.authors = copyrightValues.authors.map(author => {
                const role = author.hasOwnProperty('type') && roles.indexOf(author.type) !== -1 ? author.type : 'Licensee';
                return {
                    name: author.name,
                    role: role,
                    readonly: true,
                };
            }).reduce((accumulator, current) => {
                const exists = accumulator.filter(item => item.name === current.name && item.role === current.role);
                if (exists.length === 0) {
                    accumulator.push(current);
                }
                return accumulator;
            }, []);
        }

        this.contentBrowser.parent.metadataForm.setMetadata(metadataValues);
        this.handleDisplayCopyrightButton();
        this.metadata = copyrightValues;
    }

    handleDisplayCopyrightButton() {
        if (this.contentBrowser.widget.hasOwnProperty('$copyrightButton')) {
            this.contentBrowser.widget.$copyrightButton.hide();
        }
    }
}

class CopyrightDataObject {
    title = null;
    yearFrom = null;
    yearTo = null;
    source = null;
    version = null;
    license = null;
    authors = [];
}

export {
    AudioBrowser,
    ImageBrowser,
    VideoBrowser,
};
