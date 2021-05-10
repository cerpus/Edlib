import Joi from 'joi';
import { validateJoi } from '@cerpus/edlib-node-utils/services/index.js';
import resourceCapabilities from '../constants/resourceCapabilities.js';

const getElasticVersionFieldKey = (isListedFetch) =>
    isListedFetch ? 'publicVersion' : 'protectedVersion';

const getResourcesFromRequestValidation = (data) => {
    return validateJoi(
        data,
        Joi.object({
            limit: Joi.number().optional().min(1).default(20),
            offset: Joi.number().optional().min(0).default(0),
            orderBy: Joi.string()
                .allow('created', 'usage')
                .default('created')
                .optional(),
        })
    );
};

const getResourcesFromRequest = async (req, tenantId) => {
    const { limit, offset, orderBy } = getResourcesFromRequestValidation(
        req.query
    );

    const { body } = await req.context.services.elasticsearch.search(
        tenantId,
        {
            limit,
            offset,
        },
        {
            column: `${getElasticVersionFieldKey(tenantId === null)}.${
                orderBy === 'usage' ? 'createdAt' : 'createdAt' //@todo use correct column
            }`,
            direction: 'DESC',
        }
    );

    return {
        pagination: {
            totalCount: body.hits.total.value,
            limit,
            offset,
        },
        data: await transformElasticResources(
            req.context,
            body.hits.hits,
            !tenantId
        ),
    };
};

const transformElasticResources = async (
    context,
    elasticsearchResources,
    isPublicResources
) => {
    const resources = await context.db.resource.getByIds(
        elasticsearchResources.map((esr) => esr._source.id)
    );
    const resourceVersions = await context.db.resourceVersion.getByIds(
        elasticsearchResources.map(
            (esr) =>
                esr._source[getElasticVersionFieldKey(isPublicResources)].id
        )
    );

    return elasticsearchResources.map((esr) => {
        return {
            ...resources.find((r) => r.id === esr._source.id),
            version: resourceVersions.find(
                (rv) =>
                    rv.id ===
                    esr._source[getElasticVersionFieldKey(isPublicResources)].id
            ),
            resourceCapabilities: [
                resourceCapabilities.VIEW,
                resourceCapabilities.EDIT, //@todo fix based on type
            ],
        };
    });
};

export default {
    getResourcesFromRequest,
    transformElasticResources,
};
