import ltiService from '../services/lti.js';
import Joi from '@hapi/joi';
import { validateJoi } from '@cerpus/edlib-node-utils';

export default {
    create: async (req) => {
        let launch_presentation_locale;

        if (req.body.language) {
            launch_presentation_locale = req.body.language;
        }

        return await ltiService.createResourceRequest(
            req.context,
            req.params.externalSystemName.toLowerCase(),
            req.query.group,
            {
                jwt: req.authorizationJwt,
                userId: req.user.id,
            },
            {
                launch_presentation_return_url:
                    req.context.reqProtoHost +
                    `/lti/v2/editors/${req.params.externalSystemName.toLowerCase()}/return`,
                ext_user_id: req.user.id,
                launch_presentation_locale,
            }
        );
    },
    editorReturn: async (req, res) => {
        const { id: externalSystemId } = validateJoi(
            req.query,
            Joi.object().keys({
                id: Joi.string().required(),
            })
        );

        const resourceVersion = await req.context.services.resource.ensureResourceVersionExsists(
            req.params.externalSystemName,
            externalSystemId
        );

        res.render('editorReturn', {
            resourceId: resourceVersion.resourceId,
            resourceVersionId: resourceVersion.id,
        });
    },
    editResource: async (req, res, next) => {
        return await ltiService.editResourceRequest(
            req.context,
            req.params.resourceId,
            req.query.resourceVersionId,
            { jwt: req.authorizationJwt, userId: req.user.id, user: req.user },
            req.context.reqProtoHost,
            req.body.language
        );
    },
};
