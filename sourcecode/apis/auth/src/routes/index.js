import express from 'express';
import addContextToRequest from '../middlewares/addContextToRequest.js';
import { runAsync, logger } from '@cerpus/edlib-node-utils';
import swaggerJSDoc from 'swagger-jsdoc';
import swaggerUi from 'swagger-ui-express';
import jwksController from '../controllers/jwks.js';
import tokenController from '../controllers/token.js';
import userController from '../controllers/user.js';
import loginController from '../controllers/login.js';
import readiness from '../readiness.js';

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
     *  /v1/lti-users/token:
     *      post:
     *          description: Create an auth token for lti users
     *          produces:
     *              - application/json
     *          responses:
     *              200:
     *                  description: Successful request
     */
    apiRouter.post(
        '/v1/lti-users/token',
        runAsync(tokenController.createForLtiUser)
    );
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

    /**
     * @swagger
     *
     *  /v1/users/{id}:
     *      post:
     *          description: Get user by ID
     *          produces:
     *              - application/json
     *          parameters:
     *              - in: path
     *                name: id
     *          responses:
     *              200:
     *                  description: Successful request
     *              404:
     *                  User not found
     */
    apiRouter.get('/v1/users/:id', runAsync(userController.getUserById));
    apiRouter.get(
        '/v1/cerpusauth/login/callback',
        runAsync(loginController.cerpusAuthLoginCallback)
    );
    apiRouter.get(
        '/v1/auth-service-info',
        runAsync(loginController.getAuthServiceInfo)
    );

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
