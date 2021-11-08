import express from 'express';
import runAsync from '../services/runAsync.js';
import hasJwt from '../middlewares/hasJwt.js';
import resourceFilterController from '../controllers/resourceFilter.js';
import licenseController from '../controllers/license.js';

const { Router } = express;

/**
 * @swagger
 *
 * paths:
 *      /v1/filters/content-author-types:
 *          get:
 *              description: |
 *                  Get all the content author types
 *              produces:
 *                  - application/json
 *              responses:
 *                  200:
 *                      description: Successfully got all the content author types
 *      /v1/filters/sources:
 *          get:
 *              description: |
 *                  Get all the sources
 *              produces:
 *                  - application/json
 *              responses:
 *                  200:
 *                      description: Successfully got all the sources
 *      /v1/filters/licenses:
 *          get:
 *              description: |
 *                  Get all the licenses
 *              produces:
 *                  - application/json
 *              responses:
 *                  200:
 *                      description: Successfully got all the licenses
 */
export default async () => {
    const router = Router();

    router.get(
        '/v1/content-types/:externalSystemName',
        hasJwt,
        runAsync(resourceFilterController.getContentTypesForExternalSystemName)
    );

    router.get(
        '/v2/content-types/:externalSystemName',
        hasJwt,
        runAsync(
            resourceFilterController.getContentTypesForExternalSystemNameV2
        )
    );

    router.get(
        '/v1/filters/licenses',
        hasJwt,
        runAsync(licenseController.getAll)
    );

    return router;
};
