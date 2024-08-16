import request from 'supertest';
import { setupApp } from '../node-utils/index.js';
import router from '../routes/index.js';

export default async (clb) => {
    const compiledApp = await setupApp(() => router({}));
    return clb(request(compiledApp));
};
