import express from 'express';
import runAsync from '../services/runAsync.js';
import jobController from '../controllers/job.js';
import { middlewares } from '@cerpus/edlib-node-utils';

const { Router } = express;

export default async () => {
    const router = Router();

    router.post(
        '/v1/jobs/:jobName',
        middlewares.isUserAdmin,
        runAsync(jobController.startJob)
    );

    router.delete(
        '/v1/jobs/:jobId',
        middlewares.isUserAdmin,
        runAsync(jobController.killJob)
    );

    router.get(
        '/v1/jobs/:jobId',
        middlewares.isUserAdmin,
        runAsync(jobController.getJobStatus)
    );

    router.get(
        '/v1/jobs/:jobName/resumable',
        middlewares.isUserAdmin,
        runAsync(jobController.getResumableJob)
    );

    router.post(
        '/v1/jobs/:jobId/resume',
        middlewares.isUserAdmin,
        runAsync(jobController.resumeJob)
    );

    return router;
};
