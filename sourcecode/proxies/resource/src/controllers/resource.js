import Joi from '@hapi/joi';
import { validateJoi, constants, helpers } from '@cerpus/edlib-node-utils';

export default {
    getPreview: async (req, res, next) => {
        return req.context.services.coreExternal.preview.get(
            req.authorizationJwt,
            req.params.resourceId
        );
    },
    launchResourceEditor: async (req, res, next) => {
        const idMapping = await req.context.services.id.getForId(
            req.params.resourceId
        );

        if (
            constants.externalSystemNames.DOKU.toLowerCase() ===
            idMapping.externalSystemName.toLocaleLowerCase()
        ) {
            return {
                editor: 'doku',
                data: idMapping,
            };
        }

        const contentAuthorEditorLaunchData = await req.context.services.coreExternal.contentAuthor.edit(
            req.authorizationJwt,
            req.params.resourceId,
            req.body.translateToLanguage,
            req.body.language &&
                helpers.language.iso6391ToIETF(req.body.language)
        );

        return {
            editor: 'lti',
            data: contentAuthorEditorLaunchData,
        };
    },
    resourceCreate: async (req, res, next) => {
        if (
            constants.externalSystemNames.DOKU.toLowerCase() ===
            req.params.type.toLocaleLowerCase()
        ) {
            return {
                editor: 'doku',
            };
        }

        const contentAuthorEditorLaunchData = await req.context.services.coreExternal.contentAuthor.create(
            req.authorizationJwt,
            req.params.type,
            {
                launchPresentationLocale:
                    req.body.language &&
                    helpers.language.iso6391ToIETF(req.body.language),
            }
        );

        return {
            editor: 'lti',
            data: contentAuthorEditorLaunchData,
        };
    },
    createFromUrl: async (req, res, next) => {
        const { url } = validateJoi(
            req.body,
            Joi.object().keys({
                url: Joi.string().uri().required(),
            })
        );

        return req.context.services.coreExternal.links.createFromUrl(url);
    },
    remove: async (req, res, next) => {
        await req.context.services.coreExternal.resource.remove(
            req.params.resourceId
        );

        return {
            success: true,
        };
    },
    removeV2: async (req, res, next) => {
        await req.context.services.resource.deleteResource(
            req.user.id,
            req.params.resourceId
        );

        return {
            success: true,
        };
    },
};
