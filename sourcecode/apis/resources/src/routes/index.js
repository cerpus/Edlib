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

const { Router } = express;

export default async () => {
    const router = Router();
    const apiRouter = Router();

    apiRouter.use(
        '/docs',
        swaggerUi.serve,
        swaggerUi.setup(
            swaggerJSDoc({
                swaggerDefinition: {
                    basePath: '/dokus',
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

    apiRouter.get(
        '/v1/tenants/:tenantId/resources',
        runAsync(resourceController.getTenantResources)
    );
    apiRouter.get(
        '/v1/resources',
        runAsync(resourceController.getPublicResources)
    );
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

    router.use(addContextToRequest, apiRouter);

    return router;
};
