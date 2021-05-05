import express from 'express';
import { runAsync } from '@cerpus-private/edlib-node-utils/services/index.js';
import dokuResourcesController from '../controllers/dokuResources.js';
import auth, { authTypes } from '../middlewares/auth.js';

const { Router } = express;

/**
 * @swagger
 *
 * paths:
 *      /dokus/{id}/resources:
 *          get:
 *              description: Get all resources for a doku
 *              produces:
 *                  - application/json
 *              parameters:
 *                  - in: path
 *                    name: id
 *              responses:
 *                  200:
 *                      description: Successfully found a Doku
 *                  404:
 *                      description: Didn't find the doku
 */
export default async () => {
    const router = Router();

    router.get(
        '/dokus/:id/resources',
        auth(authTypes.CERPUS_USER),
        runAsync(dokuResourcesController.getAllForDoku)
    );

    return router;
};
