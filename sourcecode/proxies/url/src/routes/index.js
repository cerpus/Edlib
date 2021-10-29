import express from 'express';
import addContextToRequest from '../middlewares/addContextToRequest.js';
import swaggerJSDoc from 'swagger-jsdoc';
import swaggerUi from 'swagger-ui-express';
import features from '../config/features.js';
import url from './url.js';
import sync from './sync.js';
import lti from './lti.js';

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
                    basePath: '/urls',
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

    apiRouter.use(await url());
    apiRouter.use(await sync());
    apiRouter.use(await lti());

    apiRouter.get('/features', (req, res) => {
        res.json(features);
    });

    router.get('/_ah/health', (req, res) => {
        res.send('ok');
    });

    router.use('/url', addContextToRequest, apiRouter);

    return router;
};
