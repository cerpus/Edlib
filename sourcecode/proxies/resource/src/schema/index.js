import _ from 'lodash';
import SchemaMergingImport from '@graphql-toolkit/schema-merging';
import * as resourceSchema from './resource.js';

export const get = (services) => ({
    typeDefs: SchemaMergingImport.mergeTypeDefs(
        [...services.map((s) => s.schema).filter((s) => s)],
        {
            all: true,
        }
    ),
    resolvers: services.reduce(
        (resolvers, service) =>
            service.resolvers
                ? _.merge({}, resolvers, service.resolvers)
                : resolvers,
        {}
    ),
});

export default () => get([resourceSchema]);
