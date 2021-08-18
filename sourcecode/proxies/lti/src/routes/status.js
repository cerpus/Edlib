import express from 'express';
import runAsync from '../services/runAsync.js';
import StatusController from '../controllers/status.js';
import { middlewares } from '@cerpus/edlib-node-utils';

const { Router } = express;

export default async () => {
    const router = Router();

    router.get(
        '/v1/system-status',
        middlewares.isUserAdmin,
        runAsync(StatusController.systemStatus)
    );

    return router;
};
