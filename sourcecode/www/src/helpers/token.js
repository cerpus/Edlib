import decode from 'jwt-decode';
import moment from 'moment';

export const isTokenExpired = (token, marginSec = 0) => {
    const payload = decode(token);

    if (!payload) {
        return true;
    }

    return moment
        .unix(payload.exp)
        .isSameOrBefore(moment().add(marginSec, 'seconds'));
};
