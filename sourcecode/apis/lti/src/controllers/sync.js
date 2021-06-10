import { NotFoundException, pubsub } from '@cerpus/edlib-node-utils';

export default {
    getJobStatus: async (req, res, next) => {
        let syncJob = await req.context.db.sync.getById(req.params.jobId);

        if (!syncJob) {
            throw new NotFoundException('sync');
        }

        return syncJob;
    },
    syncLti: async (req, res, next) => {
        let currentSyncJob = await req.context.db.sync.getRunning();

        if (!currentSyncJob) {
            currentSyncJob = await req.context.db.sync.create({});

            await pubsub.publish(
                req.context.pubSubConnection,
                '__internal_edlibLti_sync',
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
