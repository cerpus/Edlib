import apis from '../../config/apis.js';
import axios from 'axios';
import { exceptionTranslator } from '@cerpus/edlib-node-utils';

const resourceAxios = async (options) => {
    try {
        return await axios({
            ...options,
            url: `${apis.resource.url}${options.url}`,
            maxRedirects: 0,
            timeout: 5000,
        });
    } catch (e) {
        exceptionTranslator(e);
    }
};

const getPublicResources = async (params) => {
    return (
        await resourceAxios({
            url: `/v1/resources`,
            method: 'GET',
            params,
        })
    ).data;
};

const getTenantResources = async (tenantId, params) => {
    return (
        await resourceAxios({
            url: `/v1/tenants/${tenantId}/resources`,
            method: 'GET',
            params,
        })
    ).data;
};

const getJobStatus = async (jobId) => {
    return (
        await resourceAxios({
            url: `/v1/jobs/${jobId}`,
            method: 'GET',
        })
    ).data;
};

const getResumableJob = async (jobName) => {
    return (
        await resourceAxios({
            url: `/v1/jobs/${jobName}/resumable`,
            method: 'GET',
        })
    ).data;
};

const resumeJob = async (jobId) => {
    return (
        await resourceAxios({
            url: `/v1/jobs/${jobId}/resume`,
            method: 'POST',
        })
    ).data;
};

const killJob = async (jobId) => {
    return (
        await resourceAxios({
            url: `/v1/jobs/${jobId}`,
            method: 'DELETE',
        })
    ).data;
};

const startJob = async (jobName) => {
    return (
        await resourceAxios({
            url: `/v1/jobs/${jobName}`,
            method: 'POST',
        })
    ).data;
};

const getContentTypesForExternalSystemName = async (externalSystemName) => {
    return (
        await resourceAxios({
            url: `/v1/content-types/${externalSystemName}`,
            method: 'GET',
        })
    ).data;
};

const deleteResource = async (tenantId, resourceId) => {
    return (
        await resourceAxios({
            url: `/v1/tenants/${tenantId}/resources/${resourceId}`,
            method: 'DELETE',
        })
    ).data;
};

export default () => {
    return {
        getPublicResources,
        getTenantResources,
        getContentTypesForExternalSystemName,
        getJobStatus,
        startJob,
        killJob,
        deleteResource,
        getResumableJob,
        resumeJob,
        proxy: resourceAxios,
    };
};
