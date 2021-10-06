(function ($) {
    const panelBody = $(".panel-body");
    const processSwitch = panelBody.find('#processSwitch');
    let settings = {
        url: '',
        numRowsToTraverse: 0,
        numTotal: 0,
        halt: false,
    };

    panelBody
        .on('redraw', function () {
            const total = parseInt(settings.numTotal);
            const progress = panelBody.find('.progress');
            const processed = progress.find('.progress-bar-success');
            const inprogress = progress.find('.progress-bar-warning');
            const failed = progress.find('.progress-bar-danger');
            const data = progress.data();
            processed.width(100 / total * data.success + "%");
            inprogress.width(100 / total * data.inprogress + "%");
            failed.width(100 / total * data.failed + "%");
            panelBody.find('.label-success').text(data.success);
            panelBody.find('.label-warning').text(data.inprogress);
            panelBody.find('.label-danger').text(data.failed);
            panelBody.find('.label-primary').text(settings.numTotal - data.success - data.failed);
            if ((data.success + data.failed) === total) {
                panelBody.trigger('done');
            }
        })
        .on('start', function (event, metadataSettings) {
            panelBody.find('.progress-container').removeClass('hidden');
            settings = Object.assign({}, settings, metadataSettings, {halt: false});
            processSwitch
                .removeClass('btn-primary')
                .addClass('btn-danger')
                .text("Cancel")
                .off()
                .click(() => panelBody.trigger('stop'));
            doWork(settings.numRowsToTraverse);
        })
        .on('stop', function () {
            settings.halt = true;
            processSwitch
                .text("Canceling...")
                .off();
        })
        .on('stopped', function () {
            processSwitch
                .removeClass('btn-danger')
                .addClass('btn-primary')
                .text("Start")
                .click(() => panelBody.trigger('start'));
        })
        .on('progress', function (event, data) {
            panelBody.find('.label-warning').text(data.num);
            const progress = $(this).find('.progress');
            progress.data('inprogress', data.num);
            panelBody.trigger('redraw');
        })
        .on('processed', function (event, data) {
            const progress = $(this).find('.progress');
            const progressData = progress.data();
            progressData.success += data.processed;
            progressData.inprogress -= data.processed;
            progress.data(progressData);
            panelBody.trigger('redraw');
        })
        .on('failed', function (event, data) {
            const progress = $(this).find('.progress');
            const progressData = progress.data();
            progressData.failed += data.failed.length;
            progressData.inprogress -= data.failed.length;
            progress.data(progressData);
            panelBody.find('.error-container').removeClass('hidden');
            panelBody.trigger('addFailed', {failed: data.failed});
            panelBody.trigger('redraw');
        })
        .on('done', function () {
            panelBody.find('.label-primary').text('0');
            panelBody.find('.label-warning').text('0');
            const self = $(this);
            self.addClass('disabled');
            self.find(':checked').attr('disabled', true);
        });


    function doWork(rowsInProgress) {
        panelBody.trigger('progress', {
            num: rowsInProgress
        });
        $.post(settings.url, {
                idList: $('#ids').val()
            })
            .done(function (data) {
                const failed = data.batch.filter(item => !item.success);
                const success = data.batch.filter(item => item.success);
                if (failed.length > 0) {
                    panelBody.trigger('failed', {
                        failed: failed,
                    });
                }
                if (success.length > 0) {
                    panelBody.trigger('processed', {
                        processed: success.length,
                    });
                }

                if (settings.halt === true) {
                    panelBody.trigger('stopped');
                } else if (data.outstanding > 0) {
                    doWork(Math.min(settings.numRowsToTraverse, data.outstanding));
                }
            })
            .fail(function (data) {

            });
    }
})(jQuery);
