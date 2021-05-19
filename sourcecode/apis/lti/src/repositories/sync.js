import knex, { dbHelpers } from '@cerpus/edlib-node-utils/services/db.js';

const table = 'syncs';

const create = async (sync) => {
    const [id] = await knex(table).insert(sync);

    return getById(id);
};

const update = (id, sync) => dbHelpers.updateId(table, id, sync);
const getById = async (id) => knex(table).select('*').where('id', id).first();
const getRunning = async () =>
    knex(table).select('*').whereNull('doneAt').first();

export default () => ({
    create,
    update,
    getById,
    getRunning,
});
