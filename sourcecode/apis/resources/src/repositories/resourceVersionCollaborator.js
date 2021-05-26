import knex, { dbHelpers } from '@cerpus/edlib-node-utils/services/db.js';

const table = 'resourceVersionCollaborators';

const create = async (resourceVersionCollaborator) => {
    const [id] = await knex(table).insert(resourceVersionCollaborator);

    return getById(id);
};

const update = (id, resourceVersionCollaborator) =>
    dbHelpers.updateId(table, id, resourceVersionCollaborator);

const getById = async (id) => knex(table).select('*').where('id', id).first();

const getForResourceVersion = async (resourceVersionId) =>
    knex(table).select('*').where('resourceVersionId', resourceVersionId);

const getWithTenantsForResourceVersion = async (resourceVersionId) =>
    knex(table)
        .select('*')
        .where('resourceVersionId', resourceVersionId)
        .whereNotNull('tenantId');

const getForEmailWithMissingTenantWithResourceId = async (email) =>
    knex
        .from(`${table} as rvc`)
        .select('rvc.*')
        .select('r.id as resourceId')
        .join('resourceVersions as rv', 'rv.id', 'resourceVersionId')
        .join('resources as r', 'r.id', 'rv.resourceId')
        .where('email', email)
        .whereNull('tenantId');

const remove = async (ids) => {
    if (!Array.isArray(ids)) {
        ids = [ids];
    }

    return knex(table).whereIn('id', ids).del();
};

export default () => ({
    create,
    update,
    getById,
    getForResourceVersion,
    getWithTenantsForResourceVersion,
    remove,
    getForEmailWithMissingTenantWithResourceId,
});
