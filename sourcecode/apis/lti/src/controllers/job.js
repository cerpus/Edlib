import { NotFoundException, pubsub } from '@cerpus/edlib-node-utils';
import jobNames from '../constants/jobNames.js';

export default {
    getResumableJob: async (req, res, next) => {
        let syncJob = await req.context.db.job.getLatest(req.params.jobName);

        if (!syncJob || !syncJob.resumeData || !syncJob.doneAt) {
            throw new NotFoundException('sync');
        }

        return syncJob;
    },
    getJobStatus: async (req, res, next) => {
        let syncJob = await req.context.db.job.getById(req.params.jobId);

        if (!syncJob) {
            throw new NotFoundException('sync');
        }

        return syncJob;
    },
    killJob: async (req, res, next) => {
        let syncJob = await req.context.db.job.getById(req.params.jobId);

        if (!syncJob) {
            throw new NotFoundException('sync');
        }

        return await req.context.db.job.update(req.params.jobId, {
            shouldKill: true,
        });
    },
    startJob: async (req, res, next) => {
        let currentSyncJob = await req.context.db.job.getRunning(
            req.params.jobName
        );

        if (!currentSyncJob || req.params.jobName === jobNames.IMPORT_USAGES) {
            if (Object.values(jobNames).indexOf(req.params.jobName) === -1) {
                throw new NotFoundException('job');
            }

            currentSyncJob = await req.context.db.job.create({
                type: req.params.jobName,
                data: req.body.data,
            });

            await pubsub.publish(
                req.context.pubSubConnection,
                '__internal_edlibLti_jobs_' + req.params.jobName,
                JSON.stringify({
                    jobId: currentSyncJob.id,
                })
            );
        }

        return {
            jobId: currentSyncJob.id,
        };
    },
    resumeJob: async (req, res, next) => {
        let job = await req.context.db.job.getById(req.params.jobId);

        if (!job) {
            throw new NotFoundException('job');
        }

        await req.context.db.job.update(req.params.jobId, {
            failedAt: null,
            shouldKill: false,
            doneAt: null,
        });

        await pubsub.publish(
            req.context.pubSubConnection,
            '__internal_edlibLti_jobs_' + job.type,
            JSON.stringify({
                jobId: job.id,
            })
        );

        return {
            jobId: job.id,
        };
    },
};
