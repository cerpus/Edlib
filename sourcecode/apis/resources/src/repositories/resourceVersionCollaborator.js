import { db, dbHelpers } from '@cerpus/edlib-node-utils';

const table = 'resourceVersionCollaborators';

const create = async (resourceVersionCollaborator) => {
    const [id] = await db(table).insert(resourceVersionCollaborator);

    return getById(id);
};

const update = (id, resourceVersionCollaborator) =>
    dbHelpers.updateId(table, id, resourceVersionCollaborator);

const getById = async (id) => db(table).select('*').where('id', id).first();

const getForResourceVersion = async (resourceVersionId) =>
    db(table).select('*').where('resourceVersionId', resourceVersionId);

const getWithTenantsForResourceVersion = async (resourceVersionId) =>
    db(table)
        .select('*')
        .where('resourceVersionId', resourceVersionId)
        .whereNotNull('tenantId');

const getForEmailWithMissingTenantWithResourceId = async (email) =>
    db
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

    return db(table).whereIn('id', ids).del();
};
const updateTenantIds = async (userIds) => {
    return db(table)
        .update({
            tenantId: db.raw(`CASE tenantId
                      ${userIds
                          .map((userId) =>
                              db
                                  .raw(`WHEN ? THEN ?`, [
                                      userId.from,
                                      userId.to,
                                  ])
                                  .toString()
                          )
                          .join('\n')}
                      END
    `),
        })
        .whereIn(
            'tenantId',
            userIds.map((userId) => userId.from)
        );
};

const getCountForTenants = async (ids) =>
    (
        await db(table)
            .count('*', { as: 'count' })
            .whereIn('tenantId', ids)
            .first()
    ).count;

export default () => ({
    create,
    update,
    getById,
    getForResourceVersion,
    getWithTenantsForResourceVersion,
    remove,
    getForEmailWithMissingTenantWithResourceId,
    getCountForTenants,
    updateTenantIds,
});
