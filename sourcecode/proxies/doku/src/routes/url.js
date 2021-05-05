import express from 'express';
import { runAsync } from '@cerpus-private/edlib-node-utils/services/index.js';
import urlController from '../controllers/url.js';

const { Router } = express;

/**
 * @swagger
 *
 * paths:
 *      /url/display-info:
 *          get:
 *              description: Get display information and methods for a url
 *              produces:
 *                  - application/json
 *              parameters:
 *                  - in: query
 *                    name: url
 *                    required: true
 *                  - in: query
 *                    name: method
 *              responses:
 *                  200:
 *                      description: Success
 */
export default async () => {
    const router = Router();

    router.get('/v1/url/display-info', runAsync(urlController.getDisplayInfo));

    return router;
};
