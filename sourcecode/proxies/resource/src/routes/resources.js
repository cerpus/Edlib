import express from 'express';
import runAsync from '../services/runAsync.js';
import resourceController from '../controllers/resource.js';
import recommendationController from '../controllers/recommendation.js';
import { middlewares } from '@cerpus/edlib-node-utils';

const { Router } = express;

/**
 * @swagger
 *
 * paths:
 *      /resources/{resourceId}/links:
 *          post:
 *              description: |
 *                  Create a link for a resource
 *              produces:
 *                  - application/json
 *              parameters:
 *                  - in: path
 *                    name: resourceId
 *              responses:
 *                  200:
 *                      description: Successfully created a link for a resource
 *      /resources/{resourceId}/preview:
 *          get:
 *              description: |
 *                  Get the preview for a resource
 *              produces:
 *                  - application/json
 *              parameters:
 *                  - in: path
 *                    name: resourceId
 *              responses:
 *                  200:
 *                      description: Successfully found a resource preview
 */
export default async () => {
    const router = Router();

    router.delete(
        '/v2/resources/:resourceId',
        middlewares.isUserAuthenticated,
        runAsync(resourceController.removeV2)
    );

    router.get(
        '/v2/resources',
        middlewares.isUserAuthenticated,
        runAsync(recommendationController.getV2)
    );

    return router;
};
