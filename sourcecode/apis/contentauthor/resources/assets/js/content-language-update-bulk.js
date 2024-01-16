window.H5PEditor = H5P.jQuery.extend({}, H5P, H5PEditor);
contentLanguageConfig = window.contentLanguageConfig || {};
let bulkConfig, $container;

// Initialize
$(document).ready(function () {

    // Get and reset container
    $container = $('#bulk-update-content').html('');

    bulkConfig = contentLanguageConfig;

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
        })
        .on('progress', function (event, data) {
            const progress = $(this).find('.progress');
            progress.data('inprogress', data.num);
            $(this).trigger('redraw');
        })
        .on('upgraded', function (event, data) {
            const progress = $(this).find('.progress');
            const progressData = progress.data();
            progressData.success += 1;
            progressData.inprogress -= 1;
            progress.data(progressData);
            $(this).trigger('redraw');
        })
        .on('failed', function () {
            const progress = $(this).find('.progress');
            const progressData = progress.data();
            progressData.failed += 1;
            progressData.inprogress -= 1;
            progress.data(progressData);
            $(this).trigger('redraw');
        })
        .on('done', function () {
            const self = $(this);
            self.addClass('disabled');
            self.find(':checked').attr('disabled', true);
        })
        .on('error', function (event, data) {
            const logView = $(this).find('.errorlog');
            if (data.error.code) {
                data.error.message += ` (${data.error.code})`;
            }
            logView.append('<div>Content ' + data.id + ' failed: ' + data.error.message + '</div>');
            logView.removeClass('hidden');
        });

    const events = {
        'start': function () {
            $('#bulk-container').trigger('start');
        },
        'nextbatch': function (params) {
            // const t = Object.keys(params)
            //     .map(function (key) {
            //         return params[key];
            //     })
            //     .reduce(function (o, n) {
            //         if (n.libraryId in o) {
            //             o[n.libraryId]++;
            //         } else {
            //             o[n.libraryId] = 1;
            //         }
            //         return o;
            //     }, []);
            // Object.keys(t).forEach(function (index) {
            //     $('#library_' + index).trigger('progress', {
            //         num: t[index],
            //     });
            // })
        },
        'upgraded': function (params) {
            $('#bulk-container').trigger(params.params !== false ? "upgraded" : "failed");
        },
        'terminate': function () {
            const hasErrors = $('.list-group-item .progress').filter(function () {
                return $(this).data('failed') > 0;
            }).length > 0;
            if (hasErrors) {
                $("#failedCalculations").removeClass('disabled');
            }
        },
        'error': function (params) {
            $('#bulk-container').trigger('error', params);
        },
        'status': function (data) {
            $container.append(`<div>${data.message}</div`);
        },
    };

    // Add "go" button
    $('#startRefresh')
        .removeClass('disabled')
        .click(function () {
            $container.html('<div>Calculation started</div>')
            const libraries = $('.maxScoreCheckbox:checked')
                .map(function () {
                    return this.value;
                })
                .get();
            new ContentTranslationRefreshTool(events);
        })
});

/**
 * Start a new content upgrade.
 *
 * @param {object} events
 * @returns void
 */
function ContentTranslationRefreshTool(events) {
    var self = this;

    self.started = new Date().getTime();
    // Track number of working
    self.working = 0;
    self.events = events || {};

    (function () {
        self.trigger('start');
        // Get the next batch
        self.nextBatch({
            libraryId: bulkConfig.libraryId,
            locale: bulkConfig.locale,
            token: bulkConfig.token,
            params: {},
            skipped: [],
        });
    })();
}

/**
 * Get the next batch and start processing it.
 *
 * @param {Object} outData
 */
ContentTranslationRefreshTool.prototype.nextBatch = function (outData) {
    const self = this;

    // Track time spent on IO
    const start = new Date().getTime();
    $.post(bulkConfig.endpoint, outData, function (inData) {
        if (!(inData instanceof Object)) {
            // Print errors from backend
            return self.setStatus(inData);
        }
        if (inData.left === 0) {
            const total = new Date().getTime() - self.started;

            if (window.console && console.log) {
                console.log('The upgrade process took ' + (total / 1000) + ' seconds');
            }

            self.terminate();

            // Nothing left to process
            return self.setStatus('Calculation done');
        }

        self.left = inData.left;
        self.token = inData.token;

        self.trigger('nextbatch', inData.params);
        // Start processing
        self.processBatch(inData.params, inData.skipped);
    });
};

/**
 * Set current status message.
 *
 * @param {String} msg
 */
ContentTranslationRefreshTool.prototype.setStatus = function (msg) {
    this.trigger('status', {
        message: msg
    });
};

/**
 * Process the given parameters.
 *
 * @param {Object} parameters
 * @param {Array} skipped
 */
ContentTranslationRefreshTool.prototype.processBatch = function (parameters, skipped) {
    const self = this;

    // Track upgraded params
    self.upgraded = {};
    self.skipped = skipped;

    // Track current batch
    self.parameters = parameters;

    // // Create id mapping
    // self.ids = [];
    // for (const id in parameters) {
    //     if (parameters.hasOwnProperty(id)) {
    //         self.ids.push(id);
    //     }
    // }

    // Keep track of current content
    self.current = -1;
    self.assignWork();
};

/**
 *
 */
ContentTranslationRefreshTool.prototype.assignWork = function () {
    const self = this;

    // const id = self.ids[self.current + 1];
    // const data = self.parameters[id];
    const data = self.parameters[self.current + 1];

    if (data === undefined) {
        return false; // Out of work
    }
    self.current++;
    self.working++;
console.log(data);
    self.process(data.id, JSON.parse(data.params));
};

ContentTranslationRefreshTool.prototype.fetchScript = async function (library) {
    if (H5PPresaveCache.hasOwnProperty(library)) {
        if (H5PPresaveCache[library] !== false) {
            window?.eval(H5PPresaveCache[library]);
        } else {
            throw new Error(`No script for ${library}`);
        }
    } else {
        try {
            H5PPresaveCache[library] = await $.ajax({
                method: 'GET',
                url: `${H5PLibraryPath}/${library.replace(' ', '-')}/presave.js`,
                dataType: "script",
                cache: true,
            });
        } catch {
            H5PPresaveCache[library] = false;
            throw new Error(`Failed to load script for ${library}`);
        }
    }

    return true;
};

ContentTranslationRefreshTool.prototype.failed = function (contentId, error) {
    this.trigger("error", {
        id: contentId,
        error: error,
    });
}

ContentTranslationRefreshTool.prototype.process = async function (id, data) {
    const self = this;

    // try {
        const params = await H5PEditor.ContentTranslationRefresh(data, bulkConfig.library, bulkConfig.locale);
        self.workDone(id, params);
    // } catch (e) {
    //     self.failed(id, {message : 'WIP'});
    //     self.workDone(id, null);
    // }
};

/**
 *
 */
ContentTranslationRefreshTool.prototype.workDone = function (id, result) {
    const self = this;

    self.working--;
    console.log(result);
    if (result === null) {
        self.skipped.push(id);
        result = false;
    } else {
        self.upgraded[id] = result;
    }

    self.trigger('upgraded', {
        id: id,
        params: result,
    });

    // Assign next job
    if (self.assignWork() === false && self.working === 0) {
        self.nextBatch({
            libraryId: bulkConfig.libraryId,
            locale: bulkConfig.locale,
            token: self.token,
            skipped: JSON.stringify(self.skipped),
            params: JSON.stringify(self.upgraded)
        });
    }
};

/**
 *
 */
ContentTranslationRefreshTool.prototype.terminate = function () {
    const self = this;

    self.trigger('terminate', {
        done: true
    });
};

/**
 * Trigger
 * @param action
 * @param data
 */
ContentTranslationRefreshTool.prototype.trigger = function (action, data) {
    if (typeof this.events[action] !== "undefined") {
        this.events[action].call(this, data);
    }
};
