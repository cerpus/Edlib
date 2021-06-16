import express from 'express';
import runAsync from '../services/runAsync.js';
import SyncController from '../controllers/sync.js';
import { middlewares } from '@cerpus/edlib-node-utils';

const { Router } = express;

export default async () => {
    const router = Router();

    router.post(
        '/v1/sync-resources',
        middlewares.isUserAdmin,
        runAsync(SyncController.syncResources)
    );

    router.get(
        '/v1/sync-resources/:jobId',
        middlewares.isUserAdmin,
        runAsync(SyncController.getSyncJobStatus)
    );

    return router;
};
