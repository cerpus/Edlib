import express from 'express';
import { runAsync } from '@cerpus/edlib-node-utils';
import statsController from '../controllers/stats.js';

const { Router } = express;

export default async () => {
    const router = Router();

    router.get(
        '/v1/stats/resource-version/:type/by-day',
        runAsync(statsController.getResourceVersionEventsByDay)
    );
    router.get(
        '/v1/resources/:resourceId/stats',
        runAsync(statsController.getResourceStats)
    );

    return router;
};
