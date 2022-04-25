import { setupApi, pubsub } from '@cerpus/edlib-node-utils';
import router from './routes/index.js';
import { buildRawContext } from './context/index.js';

const start = async () => {
    const pubSubConnection = await pubsub.setup();
    const context = await buildRawContext({}, {}, { pubSubConnection });
    await context.services.elasticsearch.createOrIgnoreIndex();

    setupApi(() => router({ pubSubConnection }));
};

start();
