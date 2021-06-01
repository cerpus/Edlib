import express from 'express';
import addContextToRequest from '../middlewares/addContextToRequest.js';
import { runAsync } from '@cerpus/edlib-node-utils/services/index.js';
import swaggerJSDoc from 'swagger-jsdoc';
import swaggerUi from 'swagger-ui-express';
import readiness from '../readiness.js';
import syncController from '../controllers/sync.js';
import urlController from '../controllers/url.js';
import { logger } from '@cerpus/edlib-node-utils/index.js';
import content from './content.js';

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

    apiRouter.get('/', (req, res) => {
        res.json({
            message: 'Welcome to the EdLib Url API',
        });
    });

    apiRouter.get('/v1/urls/:urlId', runAsync(urlController.getById));

    apiRouter.get(
        '/v1/sync-resources/:jobId',
        runAsync(syncController.getJobStatus)
    );
    apiRouter.post(
        '/v1/sync-resources',
        runAsync(syncController.syncResources)
    );

    apiRouter.use(await content());

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
