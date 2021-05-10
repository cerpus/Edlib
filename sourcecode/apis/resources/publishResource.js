import { setup as setupPubSub, publish } from './src/services/pubSub.js';
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
    const pubSubConnection = await setupPubSub('amqp://localhost');

    // await publish(
    //     pubSubConnection,
    //     'edlibResourceUpdate',
    //     'saveToEdlibResourcesAPI',
    //     JSON.stringify(data)
    // );
    await publish(
        pubSubConnection,
        '__internal_edlibResource_sync',
        'sync',
        ''
    );
};

run();
