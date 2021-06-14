import { db } from '@cerpus/edlib-node-utils';

const table = 'trackingResourceVersion';

const create = async (trackingResourceVersion) => {
    const [id] = await db(table).insert(trackingResourceVersion);

    return getById(id);
};

const getById = async (id) => db(table).select('*').where('id', id).first();
const getByExternalReference = async (externalReference) =>
    db(table).select('*').where('externalReference', externalReference).first();

const getCountForResource = async (resourceId) =>
    (
        await db('resourceVersions as rv')
            .count('*', { as: 'count' })
            .where('rv.resourceId', resourceId)
            .leftJoin(`${table} as trv`, 'rv.id', 'trv.resourceVersionId')
            .first()
    ).count;

export default () => ({
    create,
    getById,
    getByExternalReference,
    getCountForResource,
});
