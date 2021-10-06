H5P.externalDispatcher.on('xAPI', function (event) {
    var parentWindow = window.parent;
    if (typeof(parentWindow) !== 'undefined') {
        var statement = event.getVerifiedStatementValue([]);
        if (statement !== null) {
            var statementMessage = {
                context: 'h5p',
                action: 'statement',
                statement: statement
            };
            parentWindow.postMessage(statementMessage, '*');

            if ((event.getVerb() === 'completed' || event.getVerb() === 'answered') && !event.getVerifiedStatementValue(['context', 'contextActivities', 'parent'])) {

                var scaledScore = event.getVerifiedStatementValue(["result", "score", "scaled"]);
                if (scaledScore === null) {
                    scaledScore = event.getScore() / event.getMaxScore();
                }
                parentWindow.postMessage({
                    context: "h5p",
                    action: "score",
                    score: scaledScore,
                    score_max: event.getMaxScore(),
                    score_raw: event.getScore()
                }, "*");
            }

            if (typeof this.libraryInfo != "undefined" && this.libraryInfo.machineName == "H5P.InteractiveVideo") {
                var resetVideoProgression = function () {
                    for (var i = 0; i < H5P.instances.length; i++) {
                        var instance = H5P.instances[i];
                        if (instance.getCurrentState instanceof Function ||
                            typeof instance.getCurrentState === 'function') {
                            var state = instance.getCurrentState();
                            try {
                                if (state !== undefined && typeof state.progress != "undefined" && state.progress >= Math.floor(instance.video.getDuration())) {
                                    state.progress = 0;
                                    // Async is not used to prevent the request from being cancelled.
                                    H5P.setUserData(instance.contentId, 'state', state, {
                                        deleteOnChange: true,
                                        async: false
                                    });
                                }
                            } catch (ex) {
                            }
                        }
                    }
                }

                // iPad does not support beforeunload, therefore using unload
                H5P.$window.one('beforeunload unload', function () {
                    // Only want to do this once
                    H5P.$window.off('pagehide beforeunload unload');
                    resetVideoProgression();
                });
                // pagehide is used on iPad when tabs are switched
                H5P.$window.on('pagehide', resetVideoProgression);
            }
        } else {
            console.log('No statement');
        }
    } else {
        console.log('Unable to find parent iframe.');
    }
});

H5P.communicator.on("getScoreSetting", function (event) {
    var parentWindow = window.parent;
    var scoreStatement = {
        context: 'h5p',
        action: 'registerScoreSetting',
        canGiveScore: H5PIntegration.canGiveScore || false
    };
    parentWindow.postMessage(scoreStatement, '*');
});