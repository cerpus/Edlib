import express from 'express';
import StatusController from '../controllers/status.js';
import { middlewares, runAsync } from '@cerpus/edlib-node-utils';

const { Router } = express;

export default async () => {
    const router = Router();

    router.get(
        '/dokuapi-system-status',
        middlewares.isUserAdmin,
        runAsync(StatusController.dokuApiSystemStatus)
    );

    return router;
};
