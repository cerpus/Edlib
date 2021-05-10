import { buildRawContext } from '../context/index.js';
import apiConfig from '../config/apis.js';
import saveEdlibResourcesAPI from './saveEdlibResourcesAPI.js';
import * as elasticSearchService from '../services/elasticSearch.js';

export default async () => {
    const context = buildRawContext();

    const syncs = Object.entries(apiConfig.externalResourceAPIS).reduce(
        (syncs, [name, externalApiConfig]) => {
            if (externalApiConfig.getAllGroups) {
                syncs.push(
                    ...externalApiConfig.getAllGroups.map((groupName) => ({
                        name,
                        params: {
                            group: groupName,
                        },
                    }))
                );
            } else {
                syncs.push({
                    name,
                    params: {},
                });
            }

            return syncs;
        },
        []
    );

    for (let syncConfig of syncs) {
        let run = true;
        const limit = 100;
        let offset = 0;

        while (run) {
            const {
                resources,
            } = await context.services.externalResourceFetcher.getAll(
                syncConfig.name,
                { offset: offset, limit, ...syncConfig.params }
            );

            for (let resource of resources) {
                await saveEdlibResourcesAPI(resource, false);
            }

            if (resources.length === 0) {
                run = false;
            }

            offset = offset + limit;
        }
    }

    let run = true;
    const limit = 100;
    let offset = 0;
    while (run) {
        const resources = await context.db.resource.getAllPaginated(
            offset,
            limit
        );

        for (let resource of resources) {
            await elasticSearchService.syncResource(context, resource);
        }

        if (resources.length === 0) {
            run = false;
        }

        offset = offset + limit;
    }
};
