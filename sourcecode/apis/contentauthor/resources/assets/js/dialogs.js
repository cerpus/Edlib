function gsiLockDown() {
    var $overlay = $("<div />").addClass('overlay');
    $(document.body).append($overlay);
}

function gsiUnlockUi() {
    $("div.overlay").remove();
}

var gsiChildWindow = null;

/**
 * options = {
 *     callback: function(result) {}
 * }
 */
function gsiWindow(url, options) {
    if(!options) options = [];
    if(!options.callback) options.callback = function() {};

    gsiLockDown();
    var $container = $("<div />").addClass('window');
    var $iframe = $("<iframe />").attr('src', url);
    $container.append($iframe);
    $(document.body).append($container).css('overflow', 'hidden');
    setTimeout(function() {
        $container.addClass('visible');
    }, 10);

    gsiChildWindow = {
        options: options,
        $container: $container,
        $iframe: $iframe
    };
}

function gsiReturnHandler(val) {
    gsiChildWindow.options.callback(val);
    gsiChildWindow.$container.remove();
    gsiChildWindow = null;
    gsiUnlockUi();
}

/**
 * Called from within a dialog, and used to return a value to the parent
 * document. This closes the dialog.
 */
function gsiReturn(val) {
    parent.gsiReturnHandler(val);
}