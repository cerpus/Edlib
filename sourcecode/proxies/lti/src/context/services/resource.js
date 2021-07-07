import apis from '../../config/apis.js';
import axios from 'axios';
import { exceptionTranslator } from '@cerpus/edlib-node-utils';

const resourceAxios = async (options) => {
    try {
        return await axios({
            timeout: 5000,
            maxRedirects: 0,
            ...options,
            url: `${apis.resource.url}${options.url}`,
        });
    } catch (e) {
        exceptionTranslator(e);
    }
};

const getLtiResourceInfo = async (
    authorization,
    resourceId,
    resourceVersionId
) => {
    if (authorization.userId) {
        return (
            await resourceAxios({
                url: `/v1/tenants/${authorization.userId}/resources/${resourceId}/lti-info`,
                method: 'GET',
                params: {
                    versionId: resourceVersionId,
                },
            })
        ).data;
    }

    return (
        await resourceAxios({
            url: `/v1/resources/${resourceId}/lti-info`,
            method: 'GET',
            params: {
                versionId: resourceVersionId,
            },
        })
    ).data;
};

const getLtiCreateInfo = async (externalSystemName, group) => {
    return (
        await resourceAxios({
            url: `/v1/create-lti-info/${externalSystemName}`,
            method: 'GET',
            params: {
                group,
            },
        })
    ).data;
};

const ensureResourceVersionExsists = async (
    externalSystemName,
    externalSystemId
) => {
    return (
        await resourceAxios({
            url: `/v1/external-systems/${externalSystemName}/resources/${externalSystemId}`,
            method: 'POST',
            timeout: 30000,
        })
    ).data;
};

const getResource = async (resourceId) => {
    return (
        await resourceAxios({
            url: `/v1/resources/${resourceId}`,
            method: 'GET',
        })
    ).data;
};

const getResourceVersion = async (resourceId, resourceVersionId) => {
    return (
        await resourceAxios({
            url: `/v1/resources/${resourceId}/versions/${resourceVersionId}`,
            method: 'GET',
        })
    ).data;
};

const getLatestPublishedResourceVersion = async (resourceId) => {
    return (
        await resourceAxios({
            url: `/v1/resources/${resourceId}/version`,
            method: 'GET',
        })
    ).data;
};

const getResourceWithVersion = async (resourceId, resourceVersionId) => {
    const resource = await getResource(resourceId);

    let resourceVersion;
    if (resourceVersionId) {
        resourceVersion = await getResourceVersion(
            resourceId,
            resourceVersionId
        );
    } else {
        resourceVersion = await getLatestPublishedResourceVersion(resourceId);
    }

    return {
        ...resource,
        version: resourceVersion,
    };
};

export default () => {
    return {
        getLtiResourceInfo,
        getResourceWithVersion,
        getLtiCreateInfo,
        ensureResourceVersionExsists,
        getResource,
    };
};
