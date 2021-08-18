import express from 'express';
import recommendationController from '../controllers/recommendation.js';
import { middlewares, runAsync } from '@cerpus/edlib-node-utils';

const { Router } = express;

/**
 * @swagger
 *
 * paths:
 *      /recommendations:
 *          post:
 *              description: Get recommendations
 *              produces:
 *                  - application/json
 *              parameters:
 *                  - in: header
 *                    name: Authorization
 *                    required: true
 *              responses:
 *                  200:
 *                      description: Successfully returned recommendations
 */
export default async () => {
    const router = Router();

    router.post(
        '/v1/recommendations',
        middlewares.isUserAuthenticated,
        runAsync(recommendationController.get)
    );

    router.post(
        '/v2/recommendations',
        middlewares.isUserAuthenticated,
        runAsync(recommendationController.getV2)
    );

    return router;
};
