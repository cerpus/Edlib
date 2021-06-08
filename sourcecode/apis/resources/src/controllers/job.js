import { pubsub } from '@cerpus/edlib-node-utils/services/index.js';
import { NotFoundException } from '@cerpus/edlib-node-utils/exceptions/index.js';
import jobNames from '../constants/jobNames.js';

export default {
    getJobStatus: async (req, res, next) => {
        let syncJob = await req.context.db.job.getById(req.params.jobId);

        if (!syncJob) {
            throw new NotFoundException('sync');
        }

        return syncJob;
    },
    startJob: async (req, res, next) => {
        let currentSyncJob = await req.context.db.job.getRunning(
            req.params.jobName
        );

        if (!currentSyncJob) {
            if (Object.values(jobNames).indexOf(req.params.jobName) === -1) {
                throw new NotFoundException('job');
            }

            currentSyncJob = await req.context.db.job.create({
                type: req.params.jobName,
            });

            await pubsub.publish(
                req.context.pubSubConnection,
                '__internal_edlibResource_jobs_' + req.params.jobName,
                JSON.stringify({
                    jobId: currentSyncJob.id,
                })
            );
        }

        return {
            jobId: currentSyncJob.id,
        };
    },
};
