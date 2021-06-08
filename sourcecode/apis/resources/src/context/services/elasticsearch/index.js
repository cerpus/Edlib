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
            retry_on_conflict: 5,
        });
    };

    const createOrIgnoreIndex = async () => {
        const { body: exists } = await client.indices.exists({
            index: apiConfig.elasticsearch.resourceIndexPrefix,
        });

        if (!exists) {
            const versionProperties = {
                id: { type: 'text' },
                externalSystemName: {
                    type: 'text',
                    fields: {
                        keyword: {
                            type: 'keyword',
                        },
                    },
                },
                title: { type: 'text' },
                description: { type: 'text' },
                license: {
                    type: 'text',
                    fields: {
                        keyword: {
                            type: 'keyword',
                        },
                    },
                },
                language: {
                    type: 'text',
                    fields: {
                        keyword: {
                            type: 'keyword',
                        },
                    },
                },
                contentType: {
                    type: 'text',
                    fields: {
                        keyword: {
                            type: 'keyword',
                        },
                    },
                },
                isListed: { type: 'boolean' },
                createdAt: { type: 'date' },
                updatedAt: { type: 'date' },
            };

            await client.indices.create({
                index: apiConfig.elasticsearch.resourceIndexPrefix,
                body: {
                    mappings: {
                        dynamic: false,
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
        orderBy,
        extraQuery
    ) => {
        const field = !tenantId ? 'publicVersion' : 'protectedVersion';

        const query = {
            bool: {
                must: [
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
                    extraQuery,
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
