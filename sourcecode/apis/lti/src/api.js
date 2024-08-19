import { setupApi } from '@cerpus/edlib-node-utils';
import router from './routes/index.js';
import { pubsub } from '@cerpus/edlib-node-utils';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    setupApi(async () => router({ pubSubConnection }));
};

start();
