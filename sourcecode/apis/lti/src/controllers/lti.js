import Joi from 'joi';
import { validateJoi } from '@cerpus/edlib-node-utils';
import { NotFoundException } from '@cerpus/edlib-node-utils';
import appConfig from '../config/app.js';

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
            resourceVersionId: appConfig.features.autoUpdateLtiUsage
                ? null
                : resourceVersionId,
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
        const { offset, limit, hideTotalCount } = validateJoi(
            req.query,
            Joi.object().keys({
                offset: Joi.number().min(0).optional().default(0),
                limit: Joi.number().min(1).optional().default(100),
                hideTotalCount: Joi.boolean().optional().default(false),
            })
        );

        const usageViews = await req.context.db.usageView.getPaginatedWithResourceInfo(
            offset,
            limit
        );

        return {
            pagination: {
                count: !hideTotalCount
                    ? await req.context.db.usageView.count()
                    : undefined,
                offset: offset,
                limit: limit,
            },
            usageViews,
        };
    },
};
