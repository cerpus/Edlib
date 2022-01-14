import amqp from 'amqplib';
import logger from './logger.js';
import pubsubConfig from '../envConfig/pubsub.js';

let running = false;
let onCloseClbs = [];

export const setup = () =>
    new Promise((resolve, reject) => {
        amqp.connect(pubsubConfig.url, {
            credentials: amqp.credentials.plain(
                pubsubConfig.user,
                pubsubConfig.password
            ),
        })
            .then(function (conn) {
                running = true;

                process.once('SIGINT', function () {
                    conn.close();
                });

                conn.on('close', () => {
                    running = false;
                    onCloseClbs.forEach((clb) => clb());
                });

                resolve(conn);
            })
            .catch(reject);
    });

export const isRunning = () => running;
export const onClose = (clb) => onCloseClbs.push(clb);

export const subscribe = async (
    connection,
    exchangeName,
    subscriptionName,
    clb
) => {
    const ch = await connection.createChannel();
    ch.prefetch(1);

    return ch
        .assertExchange(exchangeName, 'fanout', {
            durable: true,
        })
        .then(function () {
            return ch.assertQueue(subscriptionName);
        })
        .then(function (qok) {
            return ch.bindQueue(qok.queue, exchangeName, '').then(function () {
                return qok.queue;
            });
        })
        .then(function (queue) {
            return ch.consume(queue, (msg) => {
                const messageData = JSON.parse(msg.content);
                logger.debug(
                    `[PubSub] Received message from topic ${exchangeName} and subscription ${subscriptionName}`,
                    {
                        rabbitMqMessage: msg.content.toString(),
                    }
                );
                let timeout = 0;
                if (messageData.__retryInfo) {
                    timeout = Math.min(
                        messageData.__retryInfo.retries * 1000,
                        10000
                    );
                }

                setTimeout(
                    () =>
                        clb(msg)
                            .then(({ requeue } = { requeue: false }) => {
                                if (requeue) {
                                    ch.reject(msg, true);
                                } else {
                                    ch.ack(msg);
                                }
                            })
                            .catch((err) => {
                                logger.error(err);
                                const unixNow = Math.round(Date.now() / 1000);
                                const newData = {
                                    ...messageData,
                                    __retryInfo: {
                                        firstTry: messageData.__retryInfo
                                            ? messageData.__retryInfo.firstTry
                                            : unixNow,
                                        retries: messageData.__retryInfo
                                            ? messageData.__retryInfo.retries +
                                              1
                                            : 1,
                                    },
                                };
                                ch.reject(msg, false);
                                if (
                                    unixNow - newData.__retryInfo.firstTry >
                                    60 * 60 // Send message to failed_messages queue after one hour
                                ) {
                                    publish(
                                        connection,
                                        'failed_messages',
                                        JSON.stringify({
                                            exchange: exchangeName,
                                            message: newData,
                                        })
                                    );
                                } else {
                                    setTimeout(
                                        () =>
                                            publish(
                                                connection,
                                                exchangeName,
                                                JSON.stringify(newData)
                                            ),
                                        500
                                    );
                                }
                            }),
                    timeout
                );
            });
        })
        .then(function () {
            logger.info(
                `[PubSub] Waiting for messages for exchange ${exchangeName} and subscription ${subscriptionName}`
            );
        });
};

export const publish = async (connection, exchangeName, message) => {
    const ch = await connection.createChannel();
    await ch.assertExchange(exchangeName, 'fanout', {
        durable: true,
    });

    ch.publish(exchangeName, '', Buffer.from(message));
    logger.debug(`[PubSub] Sent message to exchange ${exchangeName}`, {
        rabbitMqMessage: message,
    });

    return ch.close();
};
