import { Client } from '@elastic/elasticsearch';
import apiConfig from '../../../config/apis.js';

const client = new Client({ node: apiConfig.elasticsearch.url });

export default () => {
    const remove = async (resourceId) => {
        try {
            return await client.delete({
                index: apiConfig.elasticsearch.resourceIndexPrefix,
                id: resourceId,
            });
        } catch (e) {
            if (e.meta && e.meta.statusCode === 404) {
                return;
            }

            throw e;
        }
    };

    const updateOrCreate = async (resource, waitForIndex = false) => {
        return client.update({
            index: apiConfig.elasticsearch.resourceIndexPrefix,
            id: resource.id,
            body: {
                doc: resource,
                doc_as_upsert: true,
                detect_noop: !waitForIndex,
            },
            refresh: waitForIndex ? 'wait_for' : false,
        });
    };

    const createOrIgnoreIndex = async () => {
        const { body: exists } = await client.indices.exists({
            index: apiConfig.elasticsearch.resourceIndexPrefix,
        });

        if (!exists) {
            const versionProperties = {
                id: { type: 'text' },
                externalSystemName: { type: 'text' },
                title: { type: 'text' },
                description: { type: 'text' },
                license: { type: 'text' },
                language: { type: 'text' },
                contentType: { type: 'text' },
                isListed: { type: 'boolean' },
            };

            await client.indices.create({
                index: apiConfig.elasticsearch.resourceIndexPrefix,
                body: {
                    mappings: {
                        properties: {
                            id: { type: 'text' },
                            publicVersion: {
                                properties: versionProperties,
                            },
                            protectedVersion: {
                                properties: versionProperties,
                            },
                            protectedUserIds: {
                                type: 'text',
                            },
                        },
                    },
                },
            });
        }

        return true;
    };

    const search = async (
        tenantId,
        pagination = {
            limit: 20,
            offset: 0,
        },
        orderBy
    ) => {
        const field = !tenantId ? 'publicVersion' : 'protectedVersion';

        const query = {
            bool: {
                should: [
                    {
                        exists: {
                            field,
                        },
                    },
                    tenantId && {
                        match: {
                            protectedUserIds: tenantId,
                        },
                    },
                ].filter(Boolean),
            },
        };

        return client.search({
            index: apiConfig.elasticsearch.resourceIndexPrefix,
            body: {
                from: pagination.offset,
                size: pagination.limit,
                query,
                sort: [
                    {
                        [orderBy.column]: { order: orderBy.direction },
                    },
                ],
            },
        });
    };

    return {
        client,
        updateOrCreate,
        remove,
        createOrIgnoreIndex,
        search,
    };
};
