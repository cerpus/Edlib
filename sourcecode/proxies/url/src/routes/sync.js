import express from 'express';
import runAsync from '../services/runAsync.js';
import SyncController from '../controllers/sync.js';
import { isUserAdmin } from '@cerpus/edlib-node-utils/middlewares/index.js';

const { Router } = express;

export default async () => {
    const router = Router();

    router.post(
        '/v1/sync-resources',
        isUserAdmin,
        runAsync(SyncController.syncResources)
    );

    router.get(
        '/v1/sync-resources/:jobId',
        isUserAdmin,
        runAsync(SyncController.getSyncJobStatus)
    );

    return router;
};
