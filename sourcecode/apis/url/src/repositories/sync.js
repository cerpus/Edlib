import { dbHelpers, db } from '@cerpus/edlib-node-utils';

const table = 'syncs';

const create = async (sync) => {
    const [id] = await db(table).insert(sync);

    return getById(id);
};

const update = (id, sync) => dbHelpers.updateId(table, id, sync);
const getById = async (id) => db(table).select('*').where('id', id).first();
const getRunning = async () =>
    db(table).select('*').whereNull('doneAt').first();

export default () => ({
    create,
    update,
    getById,
    getRunning,
});
