
class JWTClient {
    constructor(jwtCallback) {
        this.jwt = null;
        this.jwtSourceWindow = null;
        this.jwtSourceWindowHandshake = null;

        var messageListener = function (event) {
            var data = event.data;
            if (data.type === 'jwtupdatemsg') {
                if (this.jwtSourceWindow === event.source) {
                    if (typeof(data.jwt) != 'undefined') {
                        this.jwt = data.jwt;
                        jwtCallback(data.jwt);
                    }
                } else {
                    if (data.msg === 'init' && data.key === this.jwtSourceWindowHandshake) {
                        this.jwtSourceWindow = event.source;
                    }
                }
            }
        };
        window.addEventListener('message', (function (that, listener) {
            return function (event) {
                return (listener.bind(that))(event);
            };
        })(this, messageListener));

        this.jwtSourceWindowHandshake = String(Math.random()) + '' + String(Math.random());
        window.parent.postMessage({
            'msg': 'init',
            'type': 'jwtupdatemsg',
            'key': this.jwtSourceWindowHandshake
        }, '*');
    }

    requestUpdateIfExpiringInSeconds(seconds) {
        if (this.jwt === null) {
            this.requestUpdate();
            return;
        }
        var jwtDataPart = this.jwt.split('.')[1];
        var jwtDataStr = atob(jwtDataPart);
        var jwtData = JSON.parse(jwtDataStr);

        if (typeof(jwtData.exp) !== 'undefined') {
            var currentEpochSeconds = Math.floor((new Date()).getTime() / 1000);
            var remaining = jwtData.exp - currentEpochSeconds;
            if (remaining > seconds) {
                return;
            }
        }
        this.requestUpdate();
    }

    requestUpdate() {
        if (this.jwtSourceWindow !== null) {
          this.jwtSourceWindow.postMessage({
            'msg': 'update',
            'type': 'jwtupdatemsg',
            'key': this.jwtSourceWindowHandshake
          }, '*');
        }
    }
}

window.JWTClient = JWTClient;
