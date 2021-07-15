import apis from '../../config/apis.js';
import axios from 'axios';
import { exceptionTranslator } from '@cerpus/edlib-node-utils';

const ltiAxios = async (options) => {
    try {
        return await axios({
            ...options,
            url: `${apis.lti.url}${options.url}`,
            maxRedirects: 0,
            timeout: 5000,
        });
    } catch (e) {
        exceptionTranslator(e);
    }
};

const createUsage = async (resourceId, resourceVersionId) => {
    return (
        await ltiAxios({
            url: `/v1/usages`,
            method: 'POST',
            data: {
                resourceId,
                resourceVersionId,
            },
        })
    ).data;
};

const createUsageView = async (usageId, data) => {
    return (
        await ltiAxios({
            url: `/v1/usages/${usageId}/views`,
            method: 'POST',
            data,
        })
    ).data;
};

const getUsage = async (usageId) => {
    return (
        await ltiAxios({
            url: `/v1/usages/${usageId}`,
            method: 'GET',
        })
    ).data;
};

const getConsumerByKey = async (consumerKey) => {
    return (
        await ltiAxios({
            url: `/v1/consumers/${consumerKey}`,
            method: 'GET',
        })
    ).data;
};
const getJobStatus = async (jobId) => {
    return (
        await ltiAxios({
            url: `/v1/jobs/${jobId}`,
            method: 'GET',
        })
    ).data;
};

const getResumableJob = async (jobName) => {
    return (
        await ltiAxios({
            url: `/v1/jobs/${jobName}/resumable`,
            method: 'GET',
        })
    ).data;
};

const resumeJob = async (jobId) => {
    return (
        await ltiAxios({
            url: `/v1/jobs/${jobId}/resume`,
            method: 'POST',
        })
    ).data;
};

const killJob = async (jobId) => {
    return (
        await ltiAxios({
            url: `/v1/jobs/${jobId}`,
            method: 'DELETE',
        })
    ).data;
};

const startJob = async (jobName) => {
    return (
        await ltiAxios({
            url: `/v1/jobs/${jobName}`,
            method: 'POST',
        })
    ).data;
};

export default () => {
    return {
        createUsage,
        getUsage,
        getConsumerByKey,
        createUsageView,
        getJobStatus,
        startJob,
        killJob,
        getResumableJob,
        resumeJob,
    };
};
