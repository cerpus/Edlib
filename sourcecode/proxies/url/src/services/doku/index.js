import apisConfig from '../../config/apis.js';
import axios from 'axios';
import { exceptionTranslator } from '@cerpus/edlib-node-utils';

const dokuAxios = (req) => async (options) => {
    try {
        const headers = { ...options.headers };
        if (req.user) {
            headers['x-internal-user'] = Buffer.from(
                JSON.stringify(req.user)
            ).toString('base64');
        }

        return await axios({
            ...options,
            url: `${apisConfig.doku.url}${options.url}`,
            headers,
            maxRedirects: 0,
        });
    } catch (e) {
        exceptionTranslator(e, 'Doku API');
    }
};

export default (req) => {
    const dokuApi = dokuAxios(req);

    const indexAllForRecommender = async () => {
        return (
            await dokuApi({
                url: '/api/v1/recommender/index-all',
                method: 'POST',
            })
        ).data;
    };

    const createForUser = async (userId, dokuData) => {
        return (
            await dokuApi({
                url: `/api/v1/users/${userId}/dokus`,
                method: 'POST',
                data: dokuData,
            })
        ).data;
    };

    const getForUser = async (userId, dokuId) => {
        return (
            await dokuApi({
                url: `/api/v1/users/${userId}/dokus/${dokuId}`,
                method: 'GET',
            })
        ).data;
    };

    const updateForUser = async (userId, dokuId, dokuData) => {
        return (
            await dokuApi({
                url: `/api/v1/users/${userId}/dokus/${dokuId}`,
                method: 'PATCH',
                data: dokuData,
            })
        ).data;
    };

    const publishForUser = async (userId, dokuId, data) => {
        return (
            await dokuApi({
                url: `/api/v1/users/${userId}/dokus/${dokuId}/publish`,
                method: 'POST',
                data,
            })
        ).data;
    };

    const unpublishForUser = async (userId, dokuId, data) => {
        return (
            await dokuApi({
                url: `/api/v1/users/${userId}/dokus/${dokuId}/unpublish`,
                method: 'POST',
                data,
            })
        ).data;
    };

    const getById = async (dokuId) => {
        return (
            await dokuApi({
                url: '/api/v1/dokus/' + dokuId,
                method: 'GET',
            })
        ).data;
    };

    const systemStatus = async () => {
        return (
            await dokuApi({
                url: '/api/v1/system-status',
                method: 'GET',
            })
        ).data;
    };

    return {
        createForUser,
        updateForUser,
        indexAllForRecommender,
        getById,
        publishForUser,
        unpublishForUser,
        getForUser,
        systemStatus,
    };
};
