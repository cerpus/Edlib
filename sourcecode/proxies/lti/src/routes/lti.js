import express from 'express';
import runAsync from '../services/runAsync.js';
import ltiController from '../controllers/lti.js';
import launchController from '../controllers/launch.js';
import { middlewares } from '@cerpus/edlib-node-utils';
import launchEditorController from '../controllers/launchEditor.js';
import convertExtJwtToken from '../middlewares/convertExtJwtToken.js';

const { Router } = express;

export default async () => {
    const router = Router();

    /**
     * @swagger
     *  /v2/lti/convert-launch-url:
     *      get:
     *          description: Convert an LTI launch url to a resource
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Home
     */
    router.get(
        '/v2/lti/convert-launch-url',
        middlewares.addUserToRequest,
        runAsync(ltiController.convertLaunchUrlV2)
    );

    /**
     * @swagger
     *  /v2/editors/{externalSystemName}/launch:
     *      post:
     *          description: Launch editor by name. Used for creation of new resources
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Home
     */
    router.post(
        '/v2/editors/:externalSystemName/launch',
        middlewares.isUserAuthenticated,
        runAsync(launchEditorController.create)
    );

    /**
     * @swagger
     *  /v2/resources/{resourceId}:
     *      post:
     *          description: Launch editor to edit a resource
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Home
     */
    router.post(
        '/v2/resources/:resourceId',
        middlewares.isUserAuthenticated,
        runAsync(launchEditorController.editResource)
    );

    /**
     * @swagger
     *  /v2/editors/:externalSystemName/return:
     *      get:
     *          description: Return endpoint after a resource has been created or edited. It ensures the resources is in the resource API before proceeding
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Home
     */
    router.get(
        '/v2/editors/:externalSystemName/return',
        runAsync(launchEditorController.editorReturn)
    );

    /**
     * @swagger
     *  /v2/resources/{resourceId}/preview:
     *      get:
     *          description: Preview a resource
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Home
     */
    router.get(
        '/v2/resources/:resourceId/preview',
        middlewares.isUserAuthenticated,
        runAsync(ltiController.previewLtiV2)
    );

    /**
     * @swagger
     *  /v2/resources/{resourceId}/lti-links:
     *      post:
     *          description: Create an lti link for a resource.
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Home
     */
    router.post(
        '/v2/resources/:resourceId/lti-links',
        middlewares.isUserAuthenticated,
        runAsync(launchController.createLink)
    );

    /**
     * @swagger
     *  /v2/lti-links/{usageId}:
     *      get:
     *          description: Get information about the resource behind the lti link.
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Home
     */
    router.get(
        '/v2/lti-links/:usageId',
        // middlewares.isUserAuthenticated,
        runAsync(launchController.getLink)
    );

    /**
     * @swagger
     *  /v2/lti-links/{usageId}:
     *      post:
     *          description: LTI launch
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Home
     */
    router.post(
        '/v2/lti-links/:usageId',
        convertExtJwtToken,
        runAsync(launchController.viewLink)
    );

    return router;
};
