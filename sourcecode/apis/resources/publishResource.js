import { pubsub } from '@cerpus/edlib-node-utils/services/index.js';
import { buildRawContext } from './src/context/index.js';

const data = {
    externalSystemName: 'contentAuthor',
    externalSystemId: '485',
    title: 'asdfasdf asdffff',
    ownerId: '14fec9c3-050a-4c0c-b1fb-3cd8be45682f',
    isListed: true,
    isPublished: true,
    language: 'en',
};

const run = async () => {
    const pubSubConnection = await pubsub.setup('amqp://localhost');

    // await publish(
    //     pubSubConnection,
    //     'edlibResourceUpdate',
    //     'saveToEdlibResourcesAPI',
    //     JSON.stringify(data)
    // );
    await pubsub.publish(pubSubConnection, '__internal_edlibResource_sync', '');
};

run();
