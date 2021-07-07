import schema from '../schema/index.js';
import ExpressGraphQL from 'express-graphql';
import Express from 'express';
import ApolloFederation from '@apollo/federation';

export default async () => {
    const router = Express.Router();

    router.use(
        '/graphql',
        ExpressGraphQL.graphqlHTTP((req) => ({
            schema: ApolloFederation.buildFederatedSchema(schema()),
            graphiql: true,
            context: req.context,
        }))
    );

    return router;
};
