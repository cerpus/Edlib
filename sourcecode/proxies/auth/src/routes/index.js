import express from 'express';
import addContextToRequest from '../middlewares/addContextToRequest.js';
import swaggerJSDoc from 'swagger-jsdoc';
import swaggerUi from 'swagger-ui-express';
import auth from './auth.js';
import status from './status.js';

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
                    basePath: '/auth',
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
            message: 'Welcome to the EdLib auth proxy API',
        });
    });

    apiRouter.use(await auth());
    apiRouter.use(await status());

    router.get('/_ah/health', (req, res) => {
        const probe = req.query.probe;

        if (probe === 'liveness') {
            res.send('ok');
        } else if (probe === 'readiness') {
            res.send('ok');
        } else {
            res.status(503).send();
        }
    });

    // @todo remove when after next release to prod. Remember to change endpoint in bamboo specs to /_ah/health
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

    router.use('/auth', addContextToRequest, apiRouter);

    return router;
};
