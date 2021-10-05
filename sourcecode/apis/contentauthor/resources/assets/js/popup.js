/**
 * @copyright 2015 Cerpus AS
 */

/**
 * Open a pop-up and load the href of the element inside
 *
 * @param element Element to get href and title from
 */
function openEdlibComPopUp(element) {
    var configWidth = parseInt(element.getAttribute('data-popup-width') || 0);
    var configHeight = parseInt(element.getAttribute('data-popup-height') || 0);

    var url = element.getAttribute('href');
    var title = (element.getAttribute('data-popup-title') || '');
    title = 'edlib.com' + (title.length > 0 ? ' - ' + title : '');

    // Create element to use as pop-up
    var el = document.createElement('div');
    el.id = 'edlibcom_container';
    el.style.display = 'none';
    el.style.top = 0;
    el.style.padding = 0;
    $('body').append(el);

    var size = calculateEdlibComPopUpSize(configWidth, configHeight);

    // Load the URL and open pop-up
    $('#edlibcom_container')
    .load(url, function () {
        setEdlibComPopUpSize(configWidth, configHeight, size);
    })
    .dialog({
        position: {
            my: 'center',
            at: 'center',
            of: window
        },
        resizable: false,
        autoOpen: true,
        closeOnEscape: true,
        draggable: false,
        height: 'auto',
        width: size.width,
        modal: true,
        title: title,
        close: closeEdlibComPopUp
    });

    $(window).on(
        'resize',
        {
            configWidth: parseInt(element.getAttribute('data-popup-height') || 0),
            configHeight: parseInt(element.getAttribute('data-popup-height') || 0)
        },
        resizeEdlibComPopup
    );
}

/**
 * Set the size of the popup components
 *
 * @param configWidth Value of attribute 'data-popup-width' on the triggering element
 * @param configHeight Value of attribute 'data-popup-height' on the triggering element
 * @param size Optional, the return from calculateEdlibComPopUpSize()
 */
function setEdlibComPopUpSize(configWidth, configHeight, size) {
    size = (size || calculateEdlibComPopUpSize(configWidth, configHeight));

    var popUp = $('#edlibcom_container');
    var parent = popUp.parent();
    parent.height(size.height - (parent.outerHeight() - parent.height()));
    parent.width(size.width - (parent.outerWidth() - parent.width()));
    var headerHeight = $('.ui-dialog-titlebar').outerHeight();
    $('#popup_iframe').height(parent.height() - headerHeight);
    var top = (parent.outerHeight() >= size.windowHeight ? 0 : (size.windowHeight - parent.outerHeight()) / 2);
    parent.css('top', top < 0 ? 0 : top);
    parent.css('position', 'fixed');
    // Make sure we are on top
    parent.css('z-index', 9999);
}

/**
 * Calculate height and width based on the triggering element config
 *
 * @param configWidth Value of attribute 'data-popup-width' on the triggering element
 * @param configHeight Value of attribute 'data-popup-height' on the triggering element
 * @returns {{windowHeight: (int), windowWidth: (int), width: (int), height: (int)}}
 */
function calculateEdlibComPopUpSize(configWidth, configHeight) {
    var windowHeight = $(window).height();
    var windowWidth = $(window).width();

    configWidth = (configWidth || 0);
    if (configWidth <= 0) {
        configWidth += windowWidth;
    }
    configHeight = (configHeight || 0);
    if (configHeight <= 0) {
        configHeight += windowHeight;
    }

    return {
        windowHeight: windowHeight,
        windowWidth: windowWidth,
        width: (configWidth || windowWidth) > windowWidth ? windowWidth : (configWidth || windowWidth),
        height: (configHeight || windowHeight) > windowHeight ? windowHeight : (configHeight || windowHeight)
    };
}

/**
 * Close the pop-up and destroy the pop-up container we created
 */
function closeEdlibComPopUp() {
    $(window).off('resize', resizeEdlibComPopup);
    var container = $('#edlibcom_container');
    // Remove the Iframe before destroying the dialog, if not a new request is sent
    container.find("#popup_iframe").remove();
    container.dialog('destroy').remove();
}

/**
 * Configuration:
 *   Add class 'edlibcom_popup' to the element
 *   Add attribute 'data-popup-title'="{string}" to set the title
 *   Add attribute 'data-popup-width'="{int}" to set the width, negative value is allowed
 *   Add attribute 'data-popup-height'={int}" to set the height, negative value is allowed
 *
 * Specify a negative width and/or height to specify how much smaller than the window it should be.
 * If window is 200px wide and "-20" is set as data-popup-width, the popup will be 180px wide
 */
function attachEdlibComPopUp() {
    var elements = $('.edlibcom_popup');
    for (i = 0; i < elements.length; i++) {
        elements[i].onclick = function (e) {
            e.preventDefault();
            openEdlibComPopUp(e.currentTarget);
        }
    }
}

/**
 * Resize event for the popup
 *
 * @param e Event
 */
function resizeEdlibComPopup(e) {
    setEdlibComPopUpSize(e.data.configWidth, e.data.configHeight);
}

$(function() {
    attachEdlibComPopUp();
});
