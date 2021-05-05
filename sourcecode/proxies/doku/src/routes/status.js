import express from 'express';
import StatusController from '../controllers/status.js';
import { isUserAdmin } from '@cerpus-private/edlib-node-utils/middlewares/index.js';
import { runAsync } from '@cerpus-private/edlib-node-utils/services/index.js';

const { Router } = express;

export default async () => {
    const router = Router();

    router.get(
        '/dokuapi-system-status',
        isUserAdmin,
        runAsync(StatusController.dokuApiSystemStatus)
    );

    return router;
};
