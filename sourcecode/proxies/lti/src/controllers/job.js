export default {
    killJob: async (req, res, next) => {
        return req.context.services.lti.killJob(req.params.jobId);
    },
    getJobStatus: async (req, res, next) => {
        return req.context.services.lti.getJobStatus(req.params.jobId);
    },
    startJob: async (req, res, next) => {
        return req.context.services.lti.startJob(req.params.jobName);
    },
    getResumableJob: async (req, res, next) => {
        return req.context.services.lti.getResumableJob(req.params.jobName);
    },
    resumeJob: async (req, res, next) => {
        return req.context.services.lti.resumeJob(req.params.jobId);
    },
};
