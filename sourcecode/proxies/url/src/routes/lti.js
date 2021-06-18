import express from 'express';
import { runAsync } from '@cerpus/edlib-node-utils';
import UrlController from '../controllers/url.js';

const { Router } = express;

export default async () => {
    const router = Router();

    router.post('/v1/lti-view/:urlId', runAsync(UrlController.viewUrl));

    return router;
};
