import { pubsub, setupApi } from './node-utils/index.js';
import router from './routes/index.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    setupApi(async () => router({ pubSubConnection }));
};

start();
