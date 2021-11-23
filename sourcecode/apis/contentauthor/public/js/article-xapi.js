var startTimeMs = 0;
//var firstTime = true;

var articleTitle = document.head.querySelector('meta[name="article-title"]').content;
var articleUrl = document.head.querySelector('meta[name="article-url"]').content;

function timeDiffToDuration(startMs, endMs) {
    var diffTimeS = ((endMs - startMs) / 1000).toFixed(2);

    return 'PT' + diffTimeS + 'S';
}

var articleXApiAttemptedStatement = {
    'actor': {
        'name': 'CA-User',
        'account': {
            'name': 'CA-User',
            'homePage': 'https://content-author.cerpus.com/CA-User'
        }
    },
    'verb': {
        'id': 'http://adlnet.gov/expapi/verbs/attempted',
        'display': {
            'en-US': 'attempted'
        }
    },
    'object': {
        'id': articleUrl,
        'definition': {
            'name': { 'en-US': articleTitle }
        },
        'objectType': 'Activity',
    },
    'context': {
        'contextActivities': {
            'category': [
                {
                    'id': 'http://edlib.com/resources/types/Article',
                    'objectType': 'Activity'
                }
            ],
        }
    }
};

var articleXApiCompletedStatement = {
    'actor': {
        'name': 'CA-User',
        'account': {
            'name': 'CA-User',
            'homePage': 'https://content-author.cerpus.com/CA-User'
        }
    },
    'verb': {
        'id': 'http://adlnet.gov/expapi/verbs/completed',
        'display': {
            'en-US': 'completed'
        }
    },
    'object': {
        'id': articleUrl,
        'definition': {
            'name': { 'en-US': articleTitle }
        },
        'objectType': 'Activity',
    },
    'result': {
        'duration': 'PT0S'
    },
    'context': {
        'contextActivities': {
            'category': [
                {
                    'id': 'http://edlib.com/resources/types/Article',
                    'objectType': 'Activity'
                }
            ],
        }
    }
};

function postXApiStatement(statement) {
    try {
        var parent = window.parent;
        if (typeof parent !== 'undefined' && parent !== window) {
            if (typeof statement !== 'undefined') {
                var message = {
                    'context': 'h5p',
                    'action': 'statement',
                    'statement': statement,
                };
                var msg2 = message;
                parent.postMessage(msg2, '*');
            }
        }
    } catch (e) {
        console.log('Could not post message: ' + e);
    }
}


/*
// This never fires in an iframe for some reason.
var scrollEvent = function articleXApiScrollEvent(event) {
    if (firstTime && ((window.innerHeight + window.pageYOffset) >= document.body.offsetHeight - 2)) { // Why -2? Because Safari...
        duration = timeDiffToDuration(startTimeMs, Date.now());
        var statement = articleXApiCompletedStatement;
        statement.result.duration = duration;
        console.log('Reached bottom after ' + duration);
        firstTime = false;
        postXApiStatement(statement);
    }
};
*/

var unloadEventFn = function articleXApiUnloadEvent(event) {
    var statement = articleXApiCompletedStatement;
    statement.result.duration = timeDiffToDuration(startTimeMs, Date.now());
    postXApiStatement(statement);
};

window.addEventListener('load', function (event) {
    startTimeMs = Date.now();
    // window.addEventListener('scroll', scrollEvent); // Does not fire in an iframe..
    window.addEventListener('beforeunload', unloadEventFn);

    postXApiStatement(articleXApiAttemptedStatement);
});
