import Joi from '@hapi/joi';
import { validateJoi, NotFoundException } from '@cerpus/edlib-node-utils';
import moment from 'moment-timezone';
import CryptoJS from 'crypto-js';
import oauthSignature from 'oauth-signature';
import { v4 as uuidv4 } from 'uuid';
import apiConfig from '../config/apis.js';
import ltiService from '../services/lti.js';

export default {
    launchLti: async (req, res, next) => {
        const { launchUrl, ltiUserId, cerpusUserId } = validateJoi(
            req.query,
            Joi.object().keys({
                launchUrl: Joi.string().min(1).required(),
                ltiUserId: Joi.string().min(1).optional(),
                cerpusUserId: Joi.string().min(1).optional(),
            })
        );

        const resourceLinkId = `random_link_${uuidv4()}`;

        const fields = {
            lti_message_type: 'basic-lti-launch-request',
            lti_version: 'LTI-1p0',
            context_type: 'CourseSection',
            launch_presentation_width: 850,
            launch_presentation_height: 500,
            selection_directive: true,
            ext_content_return_types:
                'url, image_url, lti_launch_url, iframe, oembed, file',
            ext_content_intended_use: 'embed',
            launch_presentation_document_target: 'iframe',
            resource_link_id: resourceLinkId,
            oauth_version: '1.0',
            oauth_nonce: CryptoJS.MD5(
                `${uuidv4()}-${Math.random()}-${process.hrtime()}`
            ).toString(),
            oauth_timestamp: moment().unix(),
            oauth_consumer_key: apiConfig.core.key,
            oauth_token: '',
            oauth_signature_method: 'HMAC-SHA1',
        };

        if (ltiUserId && cerpusUserId) {
            fields.user_id = ltiUserId;
            fields.ext_user_id = cerpusUserId;
        } else if (req.authorizationJwt) {
            fields.ext_jwt_token = req.authorizationJwt;
        }

        fields.oauth_signature = oauthSignature.generate(
            'POST',
            launchUrl,
            fields,
            apiConfig.core.secret,
            '',
            { encodeSignature: false }
        );

        return {
            url: launchUrl,
            method: 'POST',
            params: fields,
        };
    },
    previewLti: async (req, res, next) => {
        const { resourceId } = validateJoi(
            req.params,
            Joi.object().keys({
                resourceId: Joi.string().min(1).required(),
            })
        );

        return await req.context.services.coreExternal.preview.get(resourceId);
    },
    previewLtiV2: async (req, res, next) => {
        const { launchRequest } = await ltiService.viewResourceRequest(
            req.context,
            req.params.resourceId,
            req.query.resourceVersionId,
            {
                jwt: req.authorizationJwt,
                userId: req.user.id,
            },
            {
                ext_preview: 'true',
            }
        );

        return launchRequest;
    },
    convertLaunchUrl: async (req, res, next) => {
        const { launchUrl } = validateJoi(
            req.query,
            Joi.object().keys({
                launchUrl: Joi.string().uri().required(),
            })
        );

        const edlibId = await req.context.services.coreInternal.resource.convertLaunchUrlToEdlibId(
            launchUrl
        );

        return {
            edlibId,
        };
    },
    convertLaunchUrlV2: async (req, res, next) => {
        const { launchUrl } = validateJoi(
            req.query,
            Joi.object().keys({
                launchUrl: Joi.string().uri().required(),
            })
        );

        const re = /.*\/(\b[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}\b)/;
        const found = re.exec(launchUrl);

        if (!found || !found[1]) {
            throw new NotFoundException('resource');
        }

        const ltiUsageId = found[1];

        const ltiUsage = await req.context.services.lti.getUsage(ltiUsageId);

        if (!ltiUsage) {
            throw new NotFoundException('resource');
        }

        const {
            resourceVersion,
        } = await req.context.services.resource.getLtiResourceInfo(
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
