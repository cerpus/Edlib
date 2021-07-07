import Joi from '@hapi/joi';
import {
    validateJoi,
    NotFoundException,
    pubsub,
} from '@cerpus/edlib-node-utils';
import ltiService from '../services/lti.js';

export default {
    createLink: async (req) => {
        const { resourceId, resourceVersionId } = validateJoi(
            {
                resourceId: req.params.resourceId,
                resourceVersionId: req.body.resourceVersionId,
            },
            Joi.object().keys({
                resourceId: Joi.string().uuid().required(),
                resourceVersionId: Joi.string().uuid().allow(null).optional(),
            })
        );

        const {
            resourceVersion,
        } = await req.context.services.resource.getLtiResourceInfo(
            {
                jwt: req.authorizationJwt,
                userId: req.user && req.user.id,
            },
            resourceId,
            resourceVersionId
        );

        const ltiUsage = await req.context.services.lti.createUsage(
            resourceVersion.resourceId,
            resourceVersion.id
        );

        return {
            launch: `${req.protocol}://${req.get('host')}/lti/v2/lti-links/${
                ltiUsage.id
            }`,
            linkId: ltiUsage.id,
            title: resourceVersion.title,
        };
    },
    getLink: async (req) => {
        const ltiUsage = await req.context.services.lti.getUsage(
            req.params.usageId
        );

        if (!ltiUsage) {
            throw new NotFoundException('ltiUsage');
        }

        return await req.context.services.resource.getResourceWithVersion(
            ltiUsage.resourceId,
            ltiUsage.resourceVersionId
        );
    },
    viewLink: async (req, res) => {
        const params = await ltiService.validateRequest(
            req.context,
            req.context.reqProtoHost + req.originalUrl,
            req.body
        );

        const ltiUsage = await req.context.services.lti.getUsage(
            req.params.usageId
        );

        const {
            launchRequest,
            resourceVersion,
        } = await ltiService.viewResourceRequest(
            req.context,
            ltiUsage.resourceId,
            ltiUsage.resourceVersionId,
            {
                jwt: req.authorizationJwt,
                userId: req.user && req.user.id,
            },
            params
        );

        // Don't log views when in preview mode
        if (!params.ext_preview) {
            await pubsub.publish(
                req.context.pubSubConnection,
                'edlib_ltiUsageView',
                JSON.stringify({
                    resourceVersionId: resourceVersion.id,
                    usageId: req.params.usageId,
                    meta: {
                        consumerKey: params.oauth_consumer_key,
                        consumerUserId: params.user_id,
                        userId: params.ext_user_id,
                    },
                })
            );
        }

        res.render('ltiPost', {
            url: launchRequest.url,
            fields: Object.entries(launchRequest.params).map(
                ([name, value]) => ({
                    name,
                    value,
                })
            ),
        });
    },
};
