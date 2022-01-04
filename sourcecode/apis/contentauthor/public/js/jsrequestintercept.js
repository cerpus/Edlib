
var beforeSendCallbacks = null;

var addXmlHttpRequestBeforeSend = function (callback) {
    if (beforeSendCallbacks !== null) {
        beforeSendCallbacks.push(callback);
    } else {
        beforeSendCallbacks = [callback];
        var oldSend = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.send = function () {
            for (var i = 0; i < beforeSendCallbacks.length; i++) {
                beforeSendCallbacks[i](this);
            }
            oldSend.apply(this, arguments);
        }
    }
};

class XMLHttpRequestBeforeSend {
    constructor(callback) {
        addXmlHttpRequestBeforeSend((xhr) => {
            callback(xhr);
        });
    }
}

window.XMLHttpRequestBeforeSend = XMLHttpRequestBeforeSend;
