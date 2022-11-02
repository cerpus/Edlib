import express from 'express';
import { middlewares } from '@cerpus/edlib-node-utils';
import proxyRequest from '../services/proxyRequest.js';
import runAsync from "../services/runAsync.js";
import resourceController from "../controllers/resource.js"
const { Router } = express;

export default async () => {
    const router = Router();

    router.get(
        '/v1/stats/resource-version/:type/by-day',
        middlewares.isUserAdmin,
        proxyRequest(
            (req) => req.context.services.resource.proxy,
            (req) => `/v1/stats/resource-version/${req.params.type}/by-day`
        )
    );

    router.get(
        '/v1/resources/:resourceId/stats',
        middlewares.isUserAuthenticated,
        runAsync(resourceController.getStats),
    );

    router.get(
        '/v1/languages',
        middlewares.isUserAuthenticated,
        proxyRequest(
            (req) => req.context.services.resource.proxy,
            (req) => `/v1/languages`
        )
    );

    return router;
};
