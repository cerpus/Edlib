import request from 'supertest';
import { setupApp } from '@cerpus-private/edlib-node-utils/index.js';
import router from '../routes/index.js';

export default async () => {
    const compiledApp = await setupApp(router);
    return request(compiledApp);
};
