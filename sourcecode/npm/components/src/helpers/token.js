import moment from 'moment';
import decode from 'jwt-decode';

export const isTokenExpired = (token, marginSec = 0) => {
    const payload = decode(token);

    if (!payload) {
        return true;
    }

    return moment
        .unix(payload.exp)
        .isSameOrBefore(moment().add(marginSec, 'seconds'));
};
