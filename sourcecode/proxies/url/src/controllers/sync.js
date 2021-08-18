export default {
    getSyncJobStatus: async (req, res, next) => {
        return req.context.services.url.getSyncJobStatus(req.params.jobId);
    },
    syncResources: async (req, res, next) => {
        return req.context.services.url.syncResources();
    },
};
