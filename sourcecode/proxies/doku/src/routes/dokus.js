import express from 'express';
import runAsync from '../services/runAsync.js';
import dokusController from '../controllers/dokus.js';
import ltiController from '../controllers/lti.js';
import { middlewares } from '@cerpus/edlib-node-utils';

const { Router } = express;

export default async () => {
    const router = Router();

    router.post(
        '/v1/dokus',
        middlewares.isUserAuthenticated,
        runAsync(dokusController.create)
    );

    router.get(
        '/v1/dokus/:dokuId',
        middlewares.isUserAuthenticated,
        runAsync(dokusController.getById)
    );

    router.post(
        '/v1/dokus/:dokuId',
        middlewares.isUserAuthenticated,
        runAsync(dokusController.update)
    );

    router.post(
        '/v1/dokus/:dokuId/publish',
        middlewares.isUserAuthenticated,
        runAsync(dokusController.publish)
    );

    router.post(
        '/v1/dokus/:dokuId/unpublish',
        middlewares.isUserAuthenticated,
        runAsync(dokusController.unpublish)
    );

    router.get('/v1/lti/doku', runAsync(ltiController.viewDoku));

    return router;
};
