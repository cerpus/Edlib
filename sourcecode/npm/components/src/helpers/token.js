import moment from 'moment';
import jwt from 'jsonwebtoken';

export const isTokenExpired = (token, marginSec = 0) => {
    const payload = jwt.decode(token);

    if (!payload) {
        return true;
    }

    return moment
        .unix(payload.exp)
        .isSameOrBefore(moment().add(marginSec, 'seconds'));
};
