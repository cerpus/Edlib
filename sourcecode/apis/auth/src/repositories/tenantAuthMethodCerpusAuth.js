import { db } from '@cerpus/edlib-node-utils';

const table = 'tenantAuthMethodCerpusAuth';

const getByTenantAuthMethodId = async (tenantAuthMethodId) =>
    db(table)
        .select('*')
        .where('tenantAuthMethodId', tenantAuthMethodId)
        .first();

export default () => ({
    getByTenantAuthMethodId,
});
