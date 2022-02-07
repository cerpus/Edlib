import express from 'express';
import runAsync from '../services/runAsync.js';
import AuthController from '../controllers/auth.js';
import { middlewares } from '@cerpus/edlib-node-utils';

const { Router } = express;

export default async () => {
    const router = Router();

    router.post('/v1/jwt/convert', runAsync(AuthController.convert));
    router.post(
        '/v3/jwt/refresh',
        middlewares.isUserAuthenticated,
        runAsync(AuthController.refresh)
    );
    router.get(
        '/v1/me',
        middlewares.isUserAuthenticated,
        runAsync(AuthController.me)
    );
    router.get(
        '/v1/auth-service-info',
        runAsync(AuthController.getAuthServiceInfo)
    );
    router.get(
        '/v1/cerpusauth/login/callback',
        runAsync(AuthController.loginCallback)
    );

    return router;
};
