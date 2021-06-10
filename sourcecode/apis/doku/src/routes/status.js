import express from 'express';
import StatusController from '../controllers/status.js';
import { runAsync } from '@cerpus/edlib-node-utils';

const { Router } = express;

export default async () => {
    const router = Router();

    router.get('/system-status', runAsync(StatusController.systemStatus));

    return router;
};
