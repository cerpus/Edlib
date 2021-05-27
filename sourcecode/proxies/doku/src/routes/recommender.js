import express from 'express';
import runAsync from '../services/runAsync.js';
import recommenderController from '../controllers/recommender.js';
import { isUserAdmin } from '@cerpus/edlib-node-utils/middlewares/index.js';

const { Router } = express;

export default async () => {
    const router = Router();

    router.post(
        '/v1/recommender/index-all',
        isUserAdmin,
        runAsync(recommenderController.indexAll)
    );

    return router;
};
