window.H5PEditor = H5P.jQuery.extend({}, H5P, H5PEditor);

// Initialize
$(document).ready(function () {
    $("#bulk-container")
        .on('redraw', function () {
            const total = parseInt($(this).find('.progress').data().total);
            const progress = $(this).find('.progress');
            const processed = progress.find('.progress-bar-success');
            const inprogress = progress.find('.progress-bar-warning');
            const failed = progress.find('.progress-bar-danger');
            const data = progress.data();
            processed.width(100 / total * data.success + "%");
            inprogress.width(100 / total * data.inprogress + "%");
            failed.width(100 / total * data.failed + "%");
            processed.text(data.success);
            inprogress.text(data.inprogress);
            failed.text(data.failed);
            if ((data.success + data.inprogress + data.failed) === total) {
                $(this).trigger('done');
            }
        })
        .on('start', function () {
            $(this).find('.progress').removeClass('hidden');
            $(this).find('#startRefresh')
                .addClass('disabled')
                .attr('disabled', 'disabled');
            $(this).find('.bulk-update-log')
                .removeClass('hidden')
                .html('')
        })
        .on('batch', function (event, size) {
            const progress = $(this).find('.progress');
            progress.data('inprogress', size);
            $(this).trigger('redraw');
        })
        .on('progress', function (event, success) {
            const progress = $(this).find('.progress');
            const progressData = progress.data();
            if (success === true) {
                progressData.success += 1;
            } else if (success === false) {
                progressData.failed += 1;
            }
            progressData.inprogress -= 1;
            progress.data(progressData);
            $(this).trigger('redraw');
        })
        .on('done', function () {
            const self = $(this);
            self.addClass('disabled');
        })
        .on('status', function (event, message, newLine = false) {
            $('.bulk-update-log').append(`<br>${message}`);
        })
        .on('error', function (event, data) {
            const logView = $(this).find('.bulk-update-log');
            if (data.error.code) {
                data.error.message += ` (${data.error.code})`;
            }
            logView.append('<br>    ' + data.error.message);
            logView.removeClass('hidden');
        });

    // Enable 'Start' button
    $('#startRefresh')
        .removeClass('disabled')
        .removeAttr('disabled')
        .click(function () {
            new ContentTranslationRefreshTool(window.bulkTranslationConfig || {});
        })
});

/**
 * @typedef BulkTranlationRefreshConfig
 * @type {Object}
 * @property {string} ajaxPath      Where libraries and translations can be loaded from
 * @property {string} endpoint      Where content is requested and saved from
 * @property {string} library       Name of the main library in format "machinename majorversion.minorversion" e.g. "H5P.FooBar 1.42"
 * @property {number} libraryId     Id of the library
 * @property {string} locale        Locale that the translations are in
 */
/**
 * Bulk update of the translations stored in content parameters
 *
 * @param {BulkTranlationRefreshConfig} config
 * @constructor
 */
function ContentTranslationRefreshTool(config) {
    this.started = new Date().getTime();
    this.config = config;
    this.working = false;
    this.left = 0;
    this.upgraded = {};

    this.trigger('start');
    this.writeLog('Requesting content...');
    this.nextBatch();
}

ContentTranslationRefreshTool.prototype.nextBatch = function () {
    const self = this;

    const payload = {
        libraryId: this.config.libraryId,
        locale: this.config.locale,
        processed: this.upgraded,
    }

    $.post(this.config.endpoint, payload, function (inData) {
        if (!(inData instanceof Object)) {
            // Output errors from backend
            return self.writeLog(inData);
        } else if (inData.errors.length > 0) {
            inData.errors.forEach(e => self.writeLog(e));
        }
        // Nothing left to process
        if (inData.left === 0) {
            const total = new Date().getTime() - self.started;

            self.writeLog('No content received');
            self.writeLog('All content processed in ' + (total / 1000) + ' seconds');
        } else {
            self.writeLog('Got ' + inData.params.length + ' of ' + inData.left);
        }
        self.left = inData.left;

        // Start processing
        self.processBatch(inData.params);
    });
};

ContentTranslationRefreshTool.prototype.processBatch = function (parameters) {
    // Track upgraded params
    this.upgraded = {};

    // Track current batch
    this.parameters = parameters;
    this.trigger('batch', parameters.length);

    // Keep track of current content
    this.current = -1;
    this.assignWork();
};

ContentTranslationRefreshTool.prototype.assignWork = function () {
    const data = this.parameters[this.current + 1];

    if (data === undefined) {
        // Out of work
        return false;
    }
    this.current++;
    this.working = true;
    this.writeLog('Processing id ' + data.id);
    this.process(data.id, data.params);
};

ContentTranslationRefreshTool.prototype.process = async function (id, data) {
    try {
        const params = await H5PEditor.ContentTranslationRefresh(
            this.config,
            JSON.parse(data),
            msg => this.writeLog(msg)
        );
        this.trigger('progress', true);
        this.workDone(id, JSON.stringify(params));
    } catch (e) {
        console.log('Processing failed, content id ' + id);
        console.error(e);

        this.failed(id, {message : e});
        this.trigger('progress', false);
        this.workDone(id, false);
    }
};

ContentTranslationRefreshTool.prototype.workDone = function (id, result) {
    this.working = false;
    this.upgraded[id] = result;

    if (this.assignWork() === false && !this.working) {
        this.writeLog('Saving and requesting content...');
        this.nextBatch();
    }
};

ContentTranslationRefreshTool.prototype.failed = function (contentId, error) {
    this.trigger('error', {
        id: contentId,
        error: error,
    });
}

ContentTranslationRefreshTool.prototype.writeLog = function (msg) {
    this.trigger('status', msg);
};

ContentTranslationRefreshTool.prototype.trigger = function (action, data) {
    $('#bulk-container').trigger(action, data);
};
