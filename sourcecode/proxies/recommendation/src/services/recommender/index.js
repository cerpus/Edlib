import apisConfig from '../../config/apis.js';
import axios from 'axios';
import recommend from './recommend.js';

const recommenderAxios = (req) => async (options) => {
    try {
        const headers = { ...options.headers };
        if (req.authorizationJwt) {
            headers.authorization = `Bearer ${req.authorizationJwt}`;
        }

        return await axios({
            ...options,
            url: `${apisConfig.recommender.url}${options.url}`,
            headers,
            maxRedirects: 0,
        });
    } catch (e) {
        throw e;
    }
};

export default (req) => {
    const recommender = recommenderAxios(req);

    return {
        recommend: recommend(recommender),
    };
};
