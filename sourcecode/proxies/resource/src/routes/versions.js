import express from 'express';
import runAsync from '../services/runAsync.js';
import version from '../controllers/version.js';
import { middlewares } from '@cerpus/edlib-node-utils';

const { Router } = express;

/**
 * @swagger
 *
 * paths:
 *      /resources/{resourceId}/versions:
 *          post:
 *              description: |
 *                  Get versions for resource
 *              produces:
 *                  - application/json
 *              parameters:
 *                  - in: path
 *                    name: resourceId
 *              responses:
 *                  200:
 *                      description: Successfully returned versions for a resource
 */
export default async () => {
    const router = Router();

    router.get(
        '/v1/resources/:resourceId/versions',
        middlewares.isUserAuthenticated,
        runAsync(version.getForResource)
    );

    return router;
};
