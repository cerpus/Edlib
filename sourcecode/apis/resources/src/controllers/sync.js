import { publish } from '../services/pubSub.js';
import { NotFoundException } from '@cerpus/edlib-node-utils/exceptions/index.js';

export default {
    getJobStatus: async (req, res, next) => {
        let syncJob = await req.context.db.sync.getById(req.params.jobId);

        if (!syncJob) {
            throw new NotFoundException('sync');
        }

        return syncJob;
    },
    syncResources: async (req, res, next) => {
        let currentSyncJob = await req.context.db.sync.getRunning();

        if (!currentSyncJob) {
            currentSyncJob = await req.context.db.sync.create({});

            await publish(
                req.context.pubSubConnection,
                '__internal_edlibResource_sync',
                'sync',
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
