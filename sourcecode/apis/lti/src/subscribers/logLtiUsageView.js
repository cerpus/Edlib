import * as Sentry from '@sentry/node';
import { buildRawContext } from '../context/index.js';
import {
    NotFoundException,
    validateJoi,
    pubsub,
} from '@cerpus/edlib-node-utils';
import Joi from 'joi';

export default ({ pubSubConnection }) => async ({
    resourceVersionId,
    usageId,
    meta,
}) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    let { consumerKey, consumerUserId, userId } = {};

    try {
        ({ consumerKey, consumerUserId, userId } = validateJoi(
            meta,
            Joi.object().keys({
                consumerKey: Joi.string().required(),
                consumerUserId: Joi.string()
                    .allow(null)
                    .optional()
                    .default(null),
                userId: Joi.string().allow(null).optional().default(null),
            })
        ));
    } catch (e) {
        Sentry.captureException(e);
        return;
    }

    const usage = await context.db.usage.getById(usageId);

    const consumer = await context.db.consumer.getByKey(consumerKey);

    if (!consumer) {
        throw new NotFoundException('consumer');
    }

    let dbConsumerUserId = null;

    if (consumerUserId || userId) {
        let consumerUser = await context.db.consumerUser.getByConsumerAndUserId(
            consumer.id,
            consumerUserId,
            userId
        );

        if (!consumerUser) {
            consumerUser = await context.db.consumerUser.create({
                consumerId: consumer.id,
                consumerUserId,
                userId,
            });
        }

        dbConsumerUserId = consumerUser.id;
    }

    const usageView = await context.db.usageView.create({
        usageId: usage.id,
        consumerUserId: dbConsumerUserId,
    });

    await pubsub.publish(
        context.pubSubConnection,
        'edlib_trackingResourceVersion',
        JSON.stringify({
            resourceVersionId,
            externalReference: usageView.id,
        })
    );

    return usageView;
};
