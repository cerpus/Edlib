import amqp from 'amqplib';
import { logger } from '@cerpus/edlib-node-utils/index.js';

export const setup = (url = 'amqp://rabbitmq') =>
    new Promise((resolve, reject) => {
        amqp.connect(url)
            .then(function (conn) {
                process.once('SIGINT', function () {
                    conn.close();
                });

                resolve(conn);
            })
            .catch(reject);
    });

export const subscribe = async (
    connection,
    exchangeName,
    subscriptionName,
    clb
) => {
    const ch = await connection.createChannel();
    ch.prefetch(1);

    let ok = ch.assertExchange(exchangeName, 'fanout', {
        durable: true,
    });
    ok = ok.then(function () {
        return ch.assertQueue(subscriptionName);
    });
    ok = ok.then(function (qok) {
        return ch.bindQueue(qok.queue, exchangeName, '').then(function () {
            return qok.queue;
        });
    });
    ok = ok.then(function (queue) {
        return ch.consume(queue, logMessage);
    });

    return ok.then(function () {
        console.log(
            ` [*] Waiting for messages for exchange ${exchangeName} and subscription ${subscriptionName}`
        );
    });

    function logMessage(msg) {
        console.log(" [x] '%s'", msg.content.toString());
        clb(msg)
            .then(() => ch.ack(msg))
            .catch((err) => logger.error(err));
    }
};

export const publish = async (
    connection,
    exchangeName,
    subscriptionName,
    message
) => {
    const ch = await connection.createChannel();
    await ch.assertExchange(exchangeName, 'fanout', {
        durable: true,
    });

    ch.publish(exchangeName, '', Buffer.from(message));
    console.log(" [x] Sent '%s'", message);

    return ch.close();
};
