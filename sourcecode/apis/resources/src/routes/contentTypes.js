import express from 'express';
import { runAsync } from '@cerpus/edlib-node-utils/services/index.js';
import contentTypeController from "../controllers/contentType.js";

const { Router } = express;

export default async () => {
    const router = Router();

    /**
     * @swagger
     *
     *  /v1/content-types/{externalSystemName}:
     *      get:
     *          description: Get all content types for external system name
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: path
     *                name: externalSystemName
     *                type: string
     *                required: true
     *          responses:
     *              200:
     *                  description: Successful request
     *          tags:
     *              - Content types
     */
    router.get(
        '/v1/content-types/:externalSystemName',
        runAsync(contentTypeController.getContentTypesForExternalSystemName)
    );

    return router;
};
