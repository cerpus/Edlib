import { db } from '@cerpus/edlib-node-utils';

const table = 'trackingResourceVersion';

const create = async (trackingResourceVersion) => {
    const [id] = await db(table).insert(trackingResourceVersion);

    return getById(id);
};

const createManyOrIgnore = async (trackingResourceVersions) => {
    await db(table)
        .insert(trackingResourceVersions)
        .onConflict('externalReference')
        .ignore();
};

const getById = async (id) => db(table).select('*').where('id', id).first();
const getByExternalReference = async (externalReference) =>
    db(table).select('*').where('externalReference', externalReference).first();

const getCountForResource = async (resourceId) =>
    (
        await db('resourceVersions as rv')
            .count('*', { as: 'count' })
            .where('rv.resourceId', resourceId)
            .join(`${table} as trv`, 'rv.id', 'trv.resourceVersionId')
            .first()
    ).count;

const getCountByDayForResource = async (from, to, resourceId) => {
    return db(`${table} as trv`)
        .count('*', { as: 'count' })
        .select(db.raw('date(trv.createdAt) as date'))
        .join(`resourceVersions as rv`, 'rv.id', 'trv.resourceVersionId')
        .where('rv.resourceId', resourceId)
        .whereBetween('trv.createdAt', [from, to])
        .groupByRaw('date(trv.createdAt)');
};

const getCountByDay = async (from, to) => {
    return db(`${table} as trv`)
        .count('*', { as: 'count' })
        .select(db.raw('date(trv.createdAt) as date'))
        .whereBetween('trv.createdAt', [from, to])
        .groupByRaw('date(trv.createdAt)');
};

export default () => ({
    create,
    getById,
    getByExternalReference,
    getCountForResource,
    createManyOrIgnore,
    getCountByDay,
    getCountByDayForResource,
});
