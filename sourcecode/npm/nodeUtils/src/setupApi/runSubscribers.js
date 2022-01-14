import logger from '../services/logger';
import * as pubsub from '../services/pubSub';

const run = async (subscribers) => {
    const pubSubConnection = await pubsub.setup();

    await Promise.all(
        subscribers.map((subscriber) => {
            const handler = subscriber.handler({ pubSubConnection });

            return pubsub.subscribe(
                pubSubConnection,
                subscriber.exchangeName,
                subscriber.subscriptionName,
                async (msg) => {
                    await handler(JSON.parse(msg.content));
                }
            );
        })
    );

    pubsub.onClose(() => {
        logger.error(`Connection to RabbitMQ was closed`);
        process.exit(1);
    });
};

export default (subscribers) => {
    run(subscribers).catch((error) => {
        logger.error(error);
        logger.error(`Error running subscriptions`, { error });
        process.exit(1);
    });
};
