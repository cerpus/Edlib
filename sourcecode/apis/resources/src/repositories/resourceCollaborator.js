import { db } from '@cerpus/edlib-node-utils';

const table = 'resourceCollaborators';

const create = async (data) => {
    const [id] = await db(table).insert(data);

    return getById(id);
};

const getById = async (id) => db(table).select('*').where('id', id).first();

const remove = async (ids) => {
    if (!Array.isArray(ids)) {
        ids = [ids];
    }

    return db(table).whereIn('id', ids).del();
};

const getforApplicationContext = async (applicationId, context) =>
    db(table)
        .select('*')
        .where('applicationId', applicationId)
        .where('context', context);

const getForResource = async (resourceId) =>
    db(table).select('*').where('resourceId', resourceId);

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
    remove,
    getforApplicationContext,
    getForResource,
    getCountForTenants,
    updateTenantIds,
});
