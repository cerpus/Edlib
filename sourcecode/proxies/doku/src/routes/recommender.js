import express from 'express';
import runAsync from '../services/runAsync.js';
import recommenderController from '../controllers/recommender.js';
import { middlewares } from '@cerpus/edlib-node-utils';

const { Router } = express;

export default async () => {
    const router = Router();

    router.post(
        '/v1/recommender/index-all',
        middlewares.isUserAdmin,
        runAsync(recommenderController.indexAll)
    );

    return router;
};
