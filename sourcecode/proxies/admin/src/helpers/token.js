import moment from 'moment';

export const parseJwt = (token) => {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(
        atob(base64)
            .split('')
            .map(function (c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join('')
    );

    return JSON.parse(jsonPayload);
};

export const isTokenExpired = (token, marginSec = 0) => {
    const payload = parseJwt(token);

    return moment
        .unix(payload.exp)
        .isSameOrBefore(moment().add(marginSec, 'seconds'));
};
