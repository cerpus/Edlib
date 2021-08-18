import Joi from '@hapi/joi';
import { validateJoi, NotFoundException } from '@cerpus/edlib-node-utils';
import moment from 'moment-timezone';
import CryptoJS from 'crypto-js';
import oauthSignature from 'oauth-signature';
import { v4 as uuidv4 } from 'uuid';
import apiConfig from '../config/apis.js';
import ltiService from '../services/lti.js';

export default {
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
