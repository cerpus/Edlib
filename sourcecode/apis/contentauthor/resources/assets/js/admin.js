import './bootstrap';
import $ from 'jquery';
window.$ = window.jQuery = $;

$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
});

$('.install-btn')
    .each(function () {
        const element = $(this);
        element.on('click', installLibrary);
    });

$('.rebuild-btn')
    .each(function () {
        const element = $(this);
        element.on('click', rebuildLibrary);
    });

$('.delete-btn')
    .each(function () {
        const element = $(this);
        element.on('click', deleteLibrary);
    });

function sendRequest(element, optionsOrUrl, params, onDone, fail) {
    let url, method;

    if (typeof optionsOrUrl === 'string') {
        url = optionsOrUrl;
        method = 'POST';
    } else {
        url = optionsOrUrl.url;
        method = optionsOrUrl.method || 'POST';
    }

    const onFail = fail ? fail : function (response) {
        console.log(response.responseText);
        alert(response.responseJSON.message);
    };
    $.ajax(url, {
        method,
        data: params,
        dataType: "json",
    })
    .done(onDone)
    .fail(onFail)
    .always(function () {
        element.prop('disabled', null);
    });
}

function installLibrary(event) {
    const element = $(event.currentTarget);
    const url = element.data('ajax-url')
    const action = element.data('ajax-action');
    const library = element.data('name');
    const activetab = element.data('ajax-activetab');
    const errorMessage = element.data('error-message');

    if (errorMessage) {
        alert(library + '\r\n' + errorMessage);
    } else {
        element.prop('disabled', true);
        sendRequest(element, url, {
            action: action,
            machineName: library,
        }, function (response) {
            if (response.success === true) {
                alert('Library installed');
                if (typeof activetab === 'string') {
                    const params = new URLSearchParams(location.search);
                    params.set('activetab', activetab);
                    window.location.search = params.toString();
                } else {
                    window.location.reload();
                }
            } else {
                console.log(response);
                let message = '';
                if (response.message) {
                    message = response.message;
                }
                if (response.details) {
                    message += '\r\n' + response.details.join('\r\n');
                }
                alert('Library installation failed' + '\r\n' + message);
            }
        });
    }
}

function rebuildLibrary(event) {
    const element = $(event.currentTarget);
    const url = element.data('ajax-url')
    const action = element.data('ajax-action');
    const libraryId = element.data('libraryid');
    const activetab = element.data('ajax-activetab');

    element.prop('disabled', true);
    sendRequest(element, url, {
        action,
        libraryId,
    }, function (response) {
        if (response.success === true) {
            alert(response.message);
            if (typeof activetab === 'string') {
                const params = new URLSearchParams(location.search);
                params.set('activetab', activetab);
                window.location.search = params.toString();
            } else {
                window.location.reload();
            }
        } else {
            console.log(response);
        }
    });
}

function deleteLibrary(event) {
    event.preventDefault();

    const element = $(event.currentTarget);
    const url = element.data('ajax-url')
    const activetab = element.data('ajax-activetab');

    element.prop('disabled', true);
    sendRequest(
        element,
        {
            method: 'DELETE',
            url,
        },
        null,
        () => {
            alert('Library deleted');
            if (typeof activetab === 'string') {
                const params = new URLSearchParams(location.search);
                params.set('activetab', activetab);
                window.location.search = params.toString();
            } else {
                window.location.reload();
            }
        },
        (err) => {
            alert(err.responseJSON.message ?? 'Library delete failed');
        }
    );
}
