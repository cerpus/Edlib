(function () {
    window.jwt = window.sessionJwt = (function () {
        var optMeta = $('meta[name=jwt]').get(0);
        if (typeof (optMeta) !== 'undefined') {
            return $(optMeta).attr('content');
        }
    })();
    new XMLHttpRequestBeforeSend((function (xhr) {
        if (typeof (window.jwt) !== 'undefined' && window.jwt) {
            xhr.setRequestHeader('Authorization', 'Bearer ' + window.jwt);
        }
    }).bind(this));
    if (typeof (window.sessionJwt) !== 'undefined' && window.sessionJwt) {
        var jwtClient = new JWTClient((function (newJwt) {
            if (typeof (newJwt) !== 'undefined' && newJwt && newJwt !== window.sessionJwt) {
                this.jwt = newJwt;
                $.ajax({
                    'url': '/jwt/update',
                    'data': {
                        'jwt': newJwt
                    },
                    'method': 'POST',
                    'success': (function () {
                        window.sessionJwt = newJwt;
                    }).bind(this)
                });
            }
        }).bind(this));
        setInterval(function () {
            jwtClient.requestUpdateIfExpiringInSeconds(60);
        }, 1000);
    }
})();
