import { db } from '@cerpus/edlib-node-utils';

const table = 'resourceSearches';

const create = async (trackingResourceVersion) => {
    const [id] = await db(table).insert(trackingResourceVersion);

    return getById(id);
};
const getById = async (id) => db(table).select('*').where('id', id).first();

export default () => ({
    create,
    getById,
});
