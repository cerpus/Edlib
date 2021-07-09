import { validateJoi } from '@cerpus/edlib-node-utils';
import Joi from '@hapi/joi';
import { fromRecommender } from '../services/resourceConverter.js';

export default {
    get: async (req, res, next) => {
        const { h5pTypes, tags, licenses, searchString } = validateJoi(
            req.body,
            Joi.object().keys({
                h5pTypes: Joi.array().items(Joi.string()).optional(),
                tags: Joi.array().items(Joi.string()).optional(),
                licenses: Joi.array().items(Joi.string()).optional(),
                searchString: Joi.string().required(),
            })
        );

        const recommendations = await req.context.services.recommender.recommend.getRecommendation(
            {
                title: searchString,
                description: searchString,
                types: h5pTypes || [],
                tags: tags || [],
                licenses: licenses || [],
            }
        );

        return {
            data: await fromRecommender(
                req.context,
                recommendations.recommendations
            ),
        };
    },
    getV2: async (req, res, next) => {
        const { h5pTypes, tags, licenses, searchString } = validateJoi(
            req.body,
            Joi.object().keys({
                h5pTypes: Joi.array().items(Joi.string()).optional(),
                tags: Joi.array().items(Joi.string()).optional(),
                licenses: Joi.array().items(Joi.string()).optional(),
                searchString: Joi.string().required(),
            })
        );

        const recommendations = await req.context.services.recommender.recommend.getRecommendation(
            {
                title: searchString,
                description: searchString,
                types: h5pTypes || [],
                tags: tags || [],
                licenses: licenses || [],
            }
        );

        // @todo fix recommendation engine to return edlib resources. Now only works with h5p's
        const {
            resources,
        } = await req.context.services.resource.getResourcesByExternalIdReferences(
            recommendations.recommendations
                .filter(
                    (resource) => resource.id && resource.type.startsWith('h5p')
                )
                .map((resource) => ({
                    externalSystemName: 'contentauthor',
                    externalSystemId: resource.id.startsWith('h5p-')
                        ? resource.id.substring('h5p-'.length)
                        : resource.id,
                }))
        );

        return {
            data: resources,
        };
    },
};
