import JobKilledException from '../exceptions/JobKilledException.js';

export const updateJobInfo = async (context, jobId, data) => {
    if (data.resumeData) {
        data.resumeData = JSON.stringify(data.resumeData);
    }
    const { shouldKill } = await context.db.job.update(jobId, data);

    if (shouldKill) {
        throw new JobKilledException();
    }
};

export const getJobData = async (context, jobId) => {
    const job = await context.db.job.getById(jobId);

    let resumeData = null;
    if (job.resumeData) {
        try {
            resumeData = JSON.parse(job.resumeData);
        } catch (e) {}
    }

    return { resumeData, data: job.data };
};
