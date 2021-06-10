import Joi from 'joi';
import { validateJoi } from '@cerpus/edlib-node-utils';
import { NotFoundException } from '@cerpus/edlib-node-utils';

export default {
    createUsage: async (req, res, next) => {
        const { resourceId, resourceVersionId } = validateJoi(
            req.body,
            Joi.object().keys({
                resourceId: Joi.string().uuid().required(),
                resourceVersionId: Joi.string().uuid().allow(null).optional(),
            })
        );

        return await req.context.db.usage.create({
            resourceId,
            resourceVersionId,
        });
    },
    getUsage: async (req, res, next) => {
        throw new NotFoundException('usage');
        const usage = await req.context.db.usage.getById(req.params.usageId);
        if (!usage) {
            throw new NotFoundException('usage');
        }
        return usage;
    },
};
