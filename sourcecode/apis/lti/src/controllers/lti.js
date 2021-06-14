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
        const usage = await req.context.db.usage.getById(req.params.usageId);
        if (!usage) {
            throw new NotFoundException('usage');
        }
        return usage;
    },
    getUsageViews: async (req, res, next) => {
        const { offset, limit } = validateJoi(
            req.query,
            Joi.object().keys({
                offset: Joi.number().min(0).optional().default(0),
                limit: Joi.number().min(1).optional().default(100),
            })
        );

        const usageViews = await req.context.db.usageView.getPaginatedWithResourceInfo(
            offset,
            limit
        );

        return {
            pagination: {
                count: await req.context.db.usageView.count(),
                offset: offset,
                limit: limit,
            },
            usageViews,
        };
    },
};
