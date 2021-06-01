import request from 'supertest';
import { setupApp } from '@cerpus/edlib-node-utils/index.js';
import router from '../routes';

export default async (clb) => {
    const compiledApp = await setupApp(router);
    return clb(request(compiledApp));
};
