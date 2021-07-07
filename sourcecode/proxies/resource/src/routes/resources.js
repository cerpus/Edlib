import express from 'express';
import runAsync from '../services/runAsync.js';
import linkController from '../controllers/link.js';
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

    /**
     * @deprecated
     */
    router.post(
        '/v1/resources/:resourceId/lti-links',
        middlewares.isUserAuthenticated,
        runAsync(linkController.create)
    );
    /**
     * @deprecated
     */
    router.post(
        '/v1/resources/lti-links',
        middlewares.isUserAuthenticated,
        runAsync(linkController.createFromUrl)
    );
    /**
     * @deprecated
     */
    router.delete(
        '/v1/resources/:resourceId',
        middlewares.isUserAuthenticated,
        runAsync(resourceController.remove)
    );
    router.delete(
        '/v2/resources/:resourceId',
        middlewares.isUserAuthenticated,
        runAsync(resourceController.removeV2)
    );
    /**
     * @deprecated
     */
    router.get(
        '/v1/resources/:resourceId/lti-preview',
        middlewares.isUserAuthenticated,
        runAsync(resourceController.getPreview)
    );
    /**
     * @deprecated
     */
    router.post(
        '/v1/resources',
        middlewares.isUserAuthenticated,
        runAsync(recommendationController.get)
    );
    router.get(
        '/v2/resources',
        middlewares.isUserAuthenticated,
        runAsync(recommendationController.getV2)
    );
    /**
     * @deprecated
     */
    router.post(
        '/v1/resources/:resourceId/launch-editor',
        middlewares.isUserAuthenticated,
        runAsync(resourceController.launchResourceEditor)
    );
    /**
     * @deprecated
     */
    router.post(
        '/v1/launch-editor/:type',
        middlewares.isUserAuthenticated,
        runAsync(resourceController.resourceCreate)
    );

    return router;
};
