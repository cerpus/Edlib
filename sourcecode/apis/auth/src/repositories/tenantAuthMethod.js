import { db } from '@cerpus/edlib-node-utils';

const table = 'tenantAuthMethods';

const getByIssuer = async (issuer) =>
    db(table).select('*').where('issuer', issuer).first();

export default () => ({
    getByIssuer,
});
