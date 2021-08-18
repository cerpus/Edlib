export default {
    killJob: async (req, res, next) => {
        return req.context.services.resource.killJob(req.params.jobId);
    },
    getJobStatus: async (req, res, next) => {
        return req.context.services.resource.getJobStatus(req.params.jobId);
    },
    startJob: async (req, res, next) => {
        return req.context.services.resource.startJob(req.params.jobName);
    },
    getResumableJob: async (req, res, next) => {
        return req.context.services.resource.getResumableJob(
            req.params.jobName
        );
    },
    resumeJob: async (req, res, next) => {
        return req.context.services.resource.resumeJob(req.params.jobId);
    },
};
