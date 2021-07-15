import express from 'express';
import addContextToRequest from '../middlewares/addContextToRequest.js';
import swaggerJSDoc from 'swagger-jsdoc';
import swaggerUi from 'swagger-ui-express';
import versions from './versions.js';
import resources from './resources.js';
import resourceFilters from './resourceFilters.js';
import status from './status.js';
import job from './job.js';

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
                    basePath: '/resources',
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
            message: 'Welcome to the EdLib resources API',
        });
    });

    apiRouter.use(await versions());
    apiRouter.use(await resources());
    apiRouter.use(await resourceFilters());
    apiRouter.use(await status());
    apiRouter.use(await job());
    // apiRouter.use(await graphql()); // @todo uncomment when graphql is ready

    router.get('/resources/_ah/health', (req, res) => {
        const probe = req.query.probe;

        if (probe === 'liveness') {
            res.send('ok');
        } else if (probe === 'readiness') {
            res.send('ok');
        } else {
            res.status(503).send();
        }
    });

    router.use('/resources', addContextToRequest, apiRouter);

    return router;
};
