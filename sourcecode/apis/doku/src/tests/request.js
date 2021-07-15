import request from 'supertest';
import { setupApp } from '@cerpus/edlib-node-utils';
import router from '../routes/index.js';

export default async () => {
    const compiledApp = await setupApp(router);
    return request(compiledApp);
};
