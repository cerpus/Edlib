import express from 'express';
import addContextToRequest from '../middlewares/addContextToRequest.js';
import { runAsync } from '@cerpus/edlib-node-utils';
import swaggerJSDoc from 'swagger-jsdoc';
import swaggerUi from 'swagger-ui-express';
import ltiController from '../controllers/lti.js';
import usageViewController from '../controllers/usageView.js';
import consumerController from '../controllers/consumer.js';
import readiness from '../readiness.js';
import { logger } from '@cerpus/edlib-node-utils';
import syncController from '../controllers/sync.js';

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
            message: 'Welcome to the EdLib Doku API',
        });
    });

    /**
     * @swagger
     *
     *  /v1/usage-views:
     *      get:
     *          description: Get all usage views with pagination
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: query
     *                name: limit
     *                type: string
     *                default: "100"
     *                required: true
     *              - in: query
     *                name: offset
     *                type: string
     *                default: "0"
     *                required: true
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.get('/v1/usage-views', runAsync(ltiController.getUsageViews));

    apiRouter.post('/v1/usages', runAsync(ltiController.createUsage));
    apiRouter.get('/v1/usages/:usageId', runAsync(ltiController.getUsage));
    apiRouter.post(
        '/v1/usages/:usageId/views',
        runAsync(usageViewController.createUsageView)
    );
    apiRouter.get(
        '/v1/consumers/:key',
        runAsync(consumerController.getConsumerByKey)
    );
    apiRouter.get('/v1/sync-lti/:jobId', runAsync(syncController.getJobStatus));
    apiRouter.post('/v1/sync-lti', runAsync(syncController.syncLti));

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
