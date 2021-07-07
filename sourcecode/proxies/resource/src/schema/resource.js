import gql from 'graphql-tag';
import { getCapabilities } from '../services/resourceConverter.js';

export const schema = gql`
    type Query {
        resources(
            filters: ResourceFiltersInput! = {}
            pagination: PaginationInput! = {}
            sortBy: String! = "relevant"
        ): ResourcesResult!
    }

    input ResourceFiltersInput {
        contentFilter: String! = "myContent"
        h5pTypes: [String!]! = []
        keywords: [String!]! = []
        licenses: [String!]! = []
        resourceCapabilities: [String!]! = []
        sources: [String!]! = []
        search: String
    }

    input PaginationInput {
        limit: Int! = 10
        offset: Int! = 0
    }

    type PageInfo {
        offset: Int!
        limit: Int!
        totalCount: Int!
    }

    type ResourcesResult {
        pageInfo: PageInfo!
        resources: [Resource!]!
    }

    type Resource {
        id: String!
        name: String!
        type: String!
        license: ResourceLicense
        capabilities: [String!]!
        externalSystemInfo: ExternalSystemInfo!
    }

    type ResourceLicense {
        id: String!
    }

    type ExternalSystemInfo {
        systemName: String!
        id: String!
    }
`;

export const resolvers = {
    Query: {
        resources: async (parent, { filters, pagination, sortBy }, context) => {
            const recommendations = await context.services.coreExternal.recommendations.get(
                {
                    limit: pagination.limit,
                    skip: pagination.offset,
                    contentFilter: filters.contentFilter,
                    h5pTypes: filters.h5pTypes,
                    keywords: filters.keywords,
                    licenses: filters.licenses,
                    search: filters.searchString,
                    sortingOrder: sortBy,
                    sources: filters.sources,
                }
            );

            return {
                resources: recommendations.data.map((resource) => ({
                    ...resource,
                    id: resource.uuid,
                    type: resource.resourceType,
                    capabilities: getCapabilities(resource),
                })),
                pageInfo: {
                    offset: recommendations.pagination.offset,
                    limit: recommendations.pagination.limit,
                    totalCount: recommendations.pagination.totalCount,
                },
            };
        },
    },
    Resource: {
        license: async ({ id }, args, context) => {
            const license = await context.services.license.get(id);

            return license ? { id: license } : null;
        },
        externalSystemInfo: async ({ id }, args, context) => {
            const idMapping = await context.services.id.getForId(id);

            return {
                systemName: idMapping.externalSystemName,
                id: idMapping.externalSystemId,
            };
        },
    },
};
