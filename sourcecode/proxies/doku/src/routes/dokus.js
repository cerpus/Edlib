import express from 'express';
import runAsync from '../services/runAsync.js';
import dokusController from '../controllers/dokus.js';
import ltiController from '../controllers/lti.js';
import { isUserAuthenticated } from '@cerpus-private/edlib-node-utils/middlewares/index.js';

const { Router } = express;

export default async () => {
    const router = Router();

    router.post(
        '/v1/dokus',
        isUserAuthenticated,
        runAsync(dokusController.create)
    );

    router.get(
        '/v1/dokus/:dokuId',
        isUserAuthenticated,
        runAsync(dokusController.getById)
    );

    router.post(
        '/v1/dokus/:dokuId',
        isUserAuthenticated,
        runAsync(dokusController.update)
    );

    router.post(
        '/v1/dokus/:dokuId/publish',
        isUserAuthenticated,
        runAsync(dokusController.publish)
    );

    router.post(
        '/v1/dokus/:dokuId/unpublish',
        isUserAuthenticated,
        runAsync(dokusController.unpublish)
    );

    router.get('/v1/lti/doku', runAsync(ltiController.viewDoku));

    return router;
};
