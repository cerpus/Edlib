import './bootstrap';
import $ from 'jquery';

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

    element.prop('disabled', true);
    sendRequest(element, url, {
        action: action,
        machineName: library,
    }, function (response) {
        if (response.success === true) {
            alert('Library installed');
            window.location.reload();
        } else {
            console.log(response);
        }
    });
}

function rebuildLibrary(event) {
    const element = $(event.currentTarget);
    const url = element.data('ajax-url')
    const action = element.data('ajax-action');
    const libraryId = element.data('libraryid');

    element.prop('disabled', true);
    sendRequest(element, url, {
        action,
        libraryId,
    }, function (response) {
        if (response.success === true) {
            alert(response.message);
            window.location.reload();
        } else {
            console.log(response);
        }
    });
}

function deleteLibrary(event) {
    event.preventDefault();

    const element = $(event.currentTarget);
    const url = element.data('ajax-url')

    element.prop('disabled', true);
    sendRequest(element, { method: 'DELETE', url }, null, function () {
        alert('Library deleted');
        window.location.reload();
    });
}
