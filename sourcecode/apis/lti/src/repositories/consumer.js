import knex, { dbHelpers } from '@cerpus/edlib-node-utils/services/db.js';

const table = 'consumers';

const create = async (consumer) => {
    const [id] = await knex(table).insert(consumer);
    return getById(id);
};
const update = (id, consumer) => dbHelpers.updateId(table, id, consumer);
const getById = async (id) => knex(table).select('*').where('id', id).first();
const getByKey = async (key) =>
    knex(table).select('*').where('key', key).first();
const getAll = async () => knex(table).select('*');

export default () => ({
    create,
    update,
    getById,
    getByKey,
    getAll,
});
