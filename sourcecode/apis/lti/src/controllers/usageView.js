import { NotFoundException } from '@cerpus/edlib-node-utils/exceptions/index.js';
import { validateJoi } from '@cerpus/edlib-node-utils/services/index.js';
import Joi from 'joi';

export default {
    createUsageView: async (req) => {
        const { consumerKey, consumerUserId, userId } = validateJoi(
            req.body,
            Joi.object().keys({
                consumerKey: Joi.string().required(),
                consumerUserId: Joi.string()
                    .allow(null)
                    .optional()
                    .default(null),
                userId: Joi.string().allow(null).optional().default(null),
            })
        );

        const usage = await req.context.db.usage.getById(req.params.usageId);

        const consumer = await req.context.db.consumer.getByKey(consumerKey);

        if (!consumer) {
            throw new NotFoundException('consumer');
        }

        let dbConsumerUserId = null;

        if (consumerUserId || userId) {
            let consumerUser = await req.context.db.consumerUser.getByConsumerAndUserId(
                consumer.id,
                consumerUserId,
                userId
            );

            if (!consumerUser) {
                consumerUser = await req.context.db.consumerUser.create({
                    consumerId: consumer.id,
                    consumerUserId,
                    userId,
                });
            }

            dbConsumerUserId = consumerUser.id;
        }

        return await req.context.db.usageView.create({
            usageId: usage.id,
            consumerUserId: dbConsumerUserId,
        });
    },
};
