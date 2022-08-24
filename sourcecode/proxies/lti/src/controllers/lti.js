import Joi from '@hapi/joi';
import { validateJoi, NotFoundException } from '@cerpus/edlib-node-utils';
import ltiService from '../services/lti.js';

export default {
    previewLtiV2: async (req, res, next) => {
        const extras = {
            'ext_preview': 'true',
        };

        if (req.query.locale) {
            extras.launch_presentation_locale = req.query.locale;
        }

        const { launchRequest } = await ltiService.viewResourceRequest(
            req.context,
            req.params.resourceId,
            req.query.resourceVersionId,
            {
                jwt: req.authorizationJwt,
                userId: req.user.id,
            },
            extras
        );

        return launchRequest;
    },
    viewLti: async (req, res, next) => {
        const extras = {};
        if (req.query.preview) {
            extras.ext_preview = 'true';
        }

        const { launchRequest, resourceVersion } =
            await ltiService.viewResourceRequest(
                req.context,
                req.params.resourceId,
                req.query.resourceVersionId,
                req.authorizationJwt && {
                    jwt: req.authorizationJwt,
                    userId: req.user.id,
                },
                extras
            );

        return {
            ...launchRequest,
            resourceVersion,
        };
    },
    convertLaunchUrlV2: async (req, res, next) => {
        const uuidRegex =
            /^(.*\/)?(\b[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}\b)/;
        const { launchUrl } = validateJoi(
            req.query,
            Joi.object().keys({
                launchUrl: Joi.string().regex(uuidRegex).required(),
            })
        );

        const found = uuidRegex.exec(launchUrl);

        if (!found || !found[2]) {
            throw new NotFoundException('resource');
        }

        const ltiUsageId = found[2];

        const ltiUsage = await req.context.services.lti.getUsage(ltiUsageId);

        if (!ltiUsage) {
            throw new NotFoundException('resource');
        }

        const { resourceVersion } =
            await req.context.services.resource.getLtiResourceInfo(
                {
                    jwt: req.authorizationJwt,
                    userId: req.user && req.user.id,
                },
                ltiUsage.resourceId,
                ltiUsage.resourceVersionId
            );

        if (!resourceVersion) {
            throw new NotFoundException('resource');
        }

        const resource = await req.context.services.resource.getResource(
            resourceVersion.resourceId
        );

        if (!resource) {
            throw new NotFoundException('resource');
        }

        return { ...resource, version: resourceVersion };
    },
};
