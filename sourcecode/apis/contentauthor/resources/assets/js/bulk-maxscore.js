window.H5PEditor = H5P.jQuery.extend({}, H5P, H5PEditor);

var info, $container;

// Initialize
$(document).ready(function () {

    // Get and reset container
    $container = $('#h5p-admin-container').html('');

    info = CalculateScoreConfig;

    $(".list-group-item")
        .on('redraw', function () {
            const total = parseInt($(this).find('.badge').text());
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
            logView.append('<div>Content ' + data.id + ' failed: ' + data.error.message + '</div>');
            logView.removeClass('hidden');
        });

    const events = {
        'start': function (params) {
            params.forEach(function (value) {
                $('#library_' + value).trigger('start');
            });
        },
        'nextbatch': function (params) {
            const t = Object.keys(params)
                .map(function (key) {
                    return params[key];
                })
                .reduce(function (o, n) {
                    if (n.libraryId in o) {
                        o[n.libraryId]++;
                    } else {
                        o[n.libraryId] = 1;
                    }
                    return o;
                }, []);
            Object.keys(t).forEach(function (index) {
                $('#library_' + index).trigger('progress', {
                    num: t[index],
                });
            })
        },
        'upgraded': function (params) {
            $('#library_' + params.params.libraryId).trigger(params.params.success ? "upgraded" : "failed");
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
            $('#library_' + params.libraryId).trigger('error', params);
        },
        'status': function (data) {
            $container.append(`<div>${data.message}</div`);
        },
    };

    // Add "go" button
    $('#runCalculations')
        .removeClass('disabled')
        .click(function () {
            $container.html('<div>Calculation started</div>')
            const libraries = $('.maxScoreCheckbox:checked')
                .map(function () {
                    return this.value;
                })
                .get();
            new MaxScoreBulkTool(libraries, events);
        })
});

/**
 * Start a new content upgrade.
 *
 * @param {array} libraries
 * @param {object} events
 * @returns void
 */
function MaxScoreBulkTool(libraries, events) {
    var self = this;

    self.libraries = libraries;

    self.started = new Date().getTime();
    self.io = 0;

    // Track number of working
    self.working = 0;

    self.events = events || {};

    var start = function () {
        self.trigger('start', libraries);
        // Get the next batch
        self.nextBatch({
            libraries: libraries,
            token: info.token
        });
    };

    start();
}

/**
 * Get the next batch and start processing it.
 *
 * @param {Object} outData
 */
MaxScoreBulkTool.prototype.nextBatch = function (outData) {
    var self = this;

    // Track time spent on IO
    var start = new Date().getTime();
    $.post(info.endpoint, outData, function (inData) {
        self.io += new Date().getTime() - start;
        if (!(inData instanceof Object)) {
            // Print errors from backend
            return self.setStatus(inData);
        }
        if (inData.left === 0) {
            var total = new Date().getTime() - self.started;

            if (window.console && console.log) {
                console.log('The upgrade process took ' + (total / 1000) + ' seconds. (' + (Math.round((self.io / (total / 100)) * 100) / 100) + ' % IO)');
            }

            self.terminate();

            // Nothing left to process
            return self.setStatus('Calculation done');
        }

        self.left = inData.left;
        self.token = inData.token;

        self.trigger('nextbatch', inData.params);
        // Start processing
        self.processBatch(inData.params);
    });
};

/**
 * Set current status message.
 *
 * @param {String} msg
 */
MaxScoreBulkTool.prototype.setStatus = function (msg) {
    this.trigger('status', {
        message: msg
    });
};

/**
 * Process the given parameters.
 *
 * @param {Object} parameters
 */
MaxScoreBulkTool.prototype.processBatch = function (parameters) {
    var self = this;

    // Track upgraded params
    self.upgraded = {};

    // Track current batch
    self.parameters = parameters;

    // Keep track of current content
    self.current = -1;
    self.assignWork();
};

/**
 *
 */
MaxScoreBulkTool.prototype.assignWork = function () {
    var self = this;

    var data = self.parameters[self.current + 1];

    if (data === undefined) {
        return false; // Out of work
    }
    self.current++;
    self.working++;

    const { id, library, libraryId, libraryName } = data;

    // There is no version info for the libraries loaded, so we clear 'H5PPresave' to prevent using incorrect
    // versions of scripts needed for this calculation. Even if the current library is the same as previous,
    // scripts for other libraries could have been loaded.
    H5PPresave = {};

    self.fetchScript(library)
        .then(() => {
            self.process(data);
        })
        .catch(e => {
            self.failed(id, libraryId, e);
        });
};

MaxScoreBulkTool.prototype.fetchScript = async function (library) {
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
                url: '/admin/maxscore/pre-save-script',
                data: window.H5PEditor.libraryFromString(library),
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

MaxScoreBulkTool.prototype.failed = function (contentId, libraryId, error) {
    this.trigger("error", {
        id: contentId,
        libraryId: libraryId,
        error: error,
    });
    this.workDone(contentId, {
        score: 0,
        libraryId: libraryId,
        success: false,
        error: error,
    });
}

MaxScoreBulkTool.prototype.process = function (data) {
    const self = this;

    const { id, library, libraryName, libraryId, params } = data;
    const decodedParams = JSON.parse(params);

    if (typeof H5PPresave[libraryName] === 'function') {
        try {
            H5PPresave[libraryName](decodedParams, function (values) {
                self.workDone(id, {
                    score: values.maxScore,
                    libraryId: libraryId,
                    success: true,
                });
            })
        } catch (e) {
            if (window.console) {
                console.group(`Error! Library: '${library}', Content: ${id}`);
                console.error(e.message);
                console.log(decodedParams);
                console.groupEnd();
            }
            self.failed(id, libraryId, e);
        }
    } else {
        self.failed(id, libraryId, {message: `Not a function 'H5PPresave[${libraryName}]'`});
    }
};

/**
 *
 */
MaxScoreBulkTool.prototype.workDone = function (id, result) {
    var self = this;

    self.working--;
    self.upgraded[id] = result;

    self.trigger('upgraded', {
        id: id,
        params: result,
    });

    // Assign next job
    if (self.assignWork() === false && self.working === 0) {
        self.nextBatch({
            libraries: self.libraries,
            token: self.token,
            scores: JSON.stringify(self.upgraded)
        });
    }
};

/**
 *
 */
MaxScoreBulkTool.prototype.terminate = function () {
    var self = this;

    self.trigger('terminate', {
        done: true
    });
};

/**
 * Trigger
 * @param action
 * @param data
 */
MaxScoreBulkTool.prototype.trigger = function (action, data) {
    if (typeof this.events[action] !== "undefined") {
        this.events[action].call(this, data);
    }
};
