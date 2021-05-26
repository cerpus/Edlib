import express from 'express';
import addContextToRequest from '../middlewares/addContextToRequest.js';
import { runAsync } from '@cerpus/edlib-node-utils/services/index.js';
import swaggerJSDoc from 'swagger-jsdoc';
import swaggerUi from 'swagger-ui-express';
import jwksController from '../controllers/jwks.js';
import tokenController from '../controllers/token.js';
import userController from '../controllers/user.js';
import readiness from '../readiness.js';
import { logger } from '@cerpus/edlib-node-utils/index.js';

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
            message: 'Welcome to the EdLib Auth API',
        });
    });

    /**
     * @swagger
     *
     *  /.well-known/jwks.json:
     *      get:
     *          description: Get jwks keys for internal verification
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.get('/.well-known/jwks.json', runAsync(jwksController.getJwks));
    /**
     * @swagger
     *
     *  /v1/refresh-token:
     *      post:
     *          description: Refresh internal token
     *          produces:
     *              - application/json
     *          parameters:
     *              - name: token
     *                in: formData
     *                required: true
     *                type: string
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.post('/v1/refresh-token', runAsync(tokenController.refresh));
    /**
     * @swagger
     *
     *  /v1/convert-token:
     *      post:
     *          description: Convert external token to internal token
     *          produces:
     *              - application/json
     *          parameters:
     *              - name: externalToken
     *                in: formData
     *                required: true
     *                type: string
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.post('/v1/convert-token', runAsync(tokenController.convertToken));
    /**
     * @swagger
     *
     *  /v1/users-by-email:
     *      post:
     *          description: Convert external token to internal token
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: body
     *                name: request
     *                schema:
     *                  type: object
     *                  properties:
     *                      emails:
     *                          type: array
     *                          items:
     *                              type: string
     *                      required:
     *                          - email
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.post(
        '/v1/users-by-email',
        runAsync(userController.getUsersByEmail)
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

    router.use(addContextToRequest({ pubSubConnection }), apiRouter);

    return router;
};
