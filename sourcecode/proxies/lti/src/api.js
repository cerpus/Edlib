import { setupApi, pubsub } from '@cerpus/edlib-node-utils';
import router from './routes/index.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();

    setupApi(() => router({ pubSubConnection }), {
        trustProxy: true,
        extraViewDir: 'src/views',
    });
};

start();
