import express from 'express';
import addContextToRequest from '../middlewares/addContextToRequest.js';
import { runAsync, logger } from '@cerpus/edlib-node-utils';
import swaggerJSDoc from 'swagger-jsdoc';
import swaggerUi from 'swagger-ui-express';
import resourceController from '../controllers/resource.js';
import ltiController from '../controllers/lti.js';
import versionController from '../controllers/version.js';
import readiness from '../readiness.js';
import jobController from '../controllers/job.js';
import internalViewController from '../controllers/internalView.js';
import contentTypes from './contentTypes.js';
import stats from './stats.js';

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

    apiRouter.get(
        '/v1/admin/resources',
        runAsync(resourceController.adminGetAllResources)
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
        '/v1/resources-from-external/:externalSystemName/:externalSystemId/collaborators',
        runAsync(resourceController.getResourceCollaboratorsFromExternalId)
    );
    apiRouter.post(
        '/v1/resources/by-external-references',
        runAsync(resourceController.getResourceFromExternalReferences)
    );
    apiRouter.post(
        '/v1/context-resource-collaborators',
        runAsync(resourceController.setContextResourceCollaborators)
    );

    /**
     * @swagger
     *
     *  /v1/resources/{resourceId}/lti-info:
     *      get:
     *          description: Get resource info to launch a lti request
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: path
     *                name: resourceId
     *                type: string
     *                required: true
     *              - in: query
     *                name: mustBeShared
     *                type: string
     *                enum:
     *                    - "true"
     *                    - "false"
     *                default: "false"
     *                required: true
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.get(
        '/v1/resources/:resourceId/lti-info',
        runAsync(ltiController.getResourceLtiInfo)
    );

    /**
     * @swagger
     *
     *  /v1/tenants/{tenantId}/resources/{resourceId}/lti-info:
     *      get:
     *          description: Get resource info to launch a lti request and verify user has access
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: path
     *                name: tenantId
     *                type: string
     *                required: true
     *              - in: path
     *                name: resourceId
     *                type: string
     *                required: true
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.get(
        '/v1/tenants/:tenantId/resources/:resourceId/lti-info',
        runAsync(ltiController.getTenantResourceLtiInfo)
    );

    /**
     * @swagger
     *
     *  /v1/tenants/{tenantId}/resources/{resourceId}/launch-info:
     *      get:
     *          description: Get launch resource info and verify user has access
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: path
     *                name: tenantId
     *                type: string
     *                required: true
     *              - in: path
     *                name: resourceId
     *                type: string
     *                required: true
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.get(
        '/v1/tenants/:tenantId/resources/:resourceId/launch-info',
        runAsync(internalViewController.viewTenantResourceInfo)
    );

    apiRouter.get(
        '/v1/create-lti-info/:externalSystemName',
        runAsync(ltiController.getLtiCreateInfo)
    );
    apiRouter.post(
        '/v1/external-systems/:externalSystemName/resources/:externalSystemId',
        runAsync(resourceController.ensureResourceExists)
    );
    apiRouter.post('/v1/jobs/:jobName', runAsync(jobController.startJob));
    apiRouter.get(
        '/v1/jobs/:jobName/resumable',
        runAsync(jobController.getResumableJob)
    );
    apiRouter.get('/v1/jobs/:jobId', runAsync(jobController.getJobStatus));
    apiRouter.post('/v1/jobs/:jobId/resume', runAsync(jobController.resumeJob));
    apiRouter.delete('/v1/jobs/:jobId', runAsync(jobController.killJob));

    /**
     * @swagger
     *
     *  /v1/languages:
     *      get:
     *          description: Get all different languages used in Edlib
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.get('/v1/languages', runAsync(resourceController.getLanguages));

    apiRouter.use(await contentTypes());
    apiRouter.use(await stats());

    router.get('/_ah/health', (req, res) => {
        readiness()
            .then(() => res.send('ok'))
            .catch((error) => {
                logger.error(error);
                res.status(503).send();
            });
    });

    router.use(addContextToRequest({ pubSubConnection }), apiRouter);

    return router;
};
