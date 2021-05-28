import express from 'express';
import addContextToRequest from '../middlewares/addContextToRequest.js';
import { runAsync } from '@cerpus/edlib-node-utils/services/index.js';
import swaggerJSDoc from 'swagger-jsdoc';
import swaggerUi from 'swagger-ui-express';
import resourceController from '../controllers/resource.js';
import ltiController from '../controllers/lti.js';
import versionController from '../controllers/version.js';
import readiness from '../readiness.js';
import { logger } from '@cerpus/edlib-node-utils/index.js';
import syncController from '../controllers/sync.js';
import contentTypes from './contentTypes.js';

const { Router } = express;

export default async ({ pubSubConnection }) => {
    const router = Router();
    const apiRouter = Router();

    apiRouter.use(
        '/docs',
        swaggerUi.serve,
        swaggerUi.setup(
            swaggerJSDoc({
                swaggerDefinition: {
                    basePath: '/',
                },
                apis: ['./src/routes/**/*.js'],
            })
        )
    );

    /**
     * @swagger
     *
     *  /:
     *      get:
     *          description: home
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Home
     */
    apiRouter.get('/', (req, res) => {
        res.json({
            message: 'Welcome to the EdLib Resource API',
        });
    });

    /**
     * @swagger
     *
     *  /v1/tenants/{tenantId}/resources:
     *      get:
     *          description: Get resources for a tenant
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: path
     *                name: tenantId
     *                schema:
     *                  type: string
     *                required: true
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.get(
        '/v1/tenants/:tenantId/resources',
        runAsync(resourceController.getTenantResources)
    );

    /**
     * @swagger
     *
     *  /v1/tenants/{tenantId}/resources/{resourceId}:
     *      delete:
     *          description: Delete a resource for a tenant
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: path
     *                name: tenantId
     *                schema:
     *                  type: string
     *                required: true
     *              - in: path
     *                name: resourceId
     *                schema:
     *                  type: string
     *                required: true
     */
    apiRouter.delete(
        '/v1/tenants/:tenantId/resources/:resourceId',
        runAsync(resourceController.deleteTenantResource)
    );

    /**
     * @swagger
     *
     *  /v1/resources:
     *      get:
     *          description: Get public resources
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: query
     *                name: searchString
     *                type: string
     *                description: A string to search for. If nothing is provided, everything will match
     *              - in: query
     *                name: licenses
     *                type: array
     *                items:
     *                   type: string
     *                collectionFormat: multi
     *                description: A list of licenses to match. If none provided all licenses will match
     *              - in: query
     *                name: contentTypes
     *                type: array
     *                items:
     *                   type: string
     *                collectionFormat: multi
     *                description: A list of content types to match. If none provided all content types will match
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.get(
        '/v1/resources',
        runAsync(resourceController.getPublicResources)
    );

    /**
     * @swagger
     *
     *  /v1/resources/{resourceId}:
     *      get:
     *          description: Get public resources
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: path
     *                name: resourceId
     *                schema:
     *                  type: string
     *                required: true
     */
    apiRouter.get(
        '/v1/resources/:resourceId',
        runAsync(resourceController.getResource)
    );
    apiRouter.get(
        '/v1/resources/:resourceId/version',
        runAsync(versionController.findCurrentResourceVersion)
    );
    apiRouter.get(
        '/v1/resources/:resourceId/versions/:resourceVersionId',
        runAsync(versionController.getResourceVersion)
    );
    apiRouter.get(
        '/v1/resources-from-external/:externalSystemName/:externalSystemId',
        runAsync(resourceController.getResourceFromExternalId)
    );
    apiRouter.get(
        '/v1/resources/:resourceId/lti-info',
        runAsync(ltiController.getResourceLtiInfo)
    );
    apiRouter.get(
        '/v1/create-lti-info/:externalSystemName',
        runAsync(ltiController.getLtiCreateInfo)
    );
    apiRouter.post(
        '/v1/external-systems/:externalSystemName/resources/:externalSystemId',
        runAsync(resourceController.ensureResourceExists)
    );
    apiRouter.get(
        '/v1/sync-resources/:jobId',
        runAsync(syncController.getJobStatus)
    );
    apiRouter.post(
        '/v1/sync-resources',
        runAsync(syncController.syncResources)
    );

    apiRouter.use(await contentTypes());

    router.get('/_ah/health', (req, res) => {
        const probe = req.query.probe;

        if (probe === 'readiness') {
            readiness()
                .then(() => res.send('ok'))
                .catch((error) => {
                    logger.error(error);
                    res.status(503).send();
                });
        } else {
            res.status(503).send();
        }
    });

    router.use(addContextToRequest({ pubSubConnection }), apiRouter);

    return router;
};
