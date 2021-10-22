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
import jobController from '../controllers/job.js';

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

    apiRouter.post('/v1/jobs/:jobName', runAsync(jobController.startJob));
    apiRouter.get(
        '/v1/jobs/:jobName/resumable',
        runAsync(jobController.getResumableJob)
    );
    apiRouter.get('/v1/jobs/:jobId', runAsync(jobController.getJobStatus));
    apiRouter.post('/v1/jobs/:jobId/resume', runAsync(jobController.resumeJob));
    apiRouter.delete('/v1/jobs/:jobId', runAsync(jobController.killJob));

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
