import knex, { dbHelpers } from '@cerpus/edlib-node-utils/services/db.js';

const table = 'urls';

const create = async (url) => dbHelpers.create(table, url);
const update = (id, url) => dbHelpers.updateId(table, id, url);

const getById = async (id) => knex(table).select('*').where('id', id).first();
const getAll = async (limit, offset) =>
    knex(table).select('*').offset(offset).limit(limit);
const count = async () => knex(table).count('*', { as: 'count' }).first();

export default () => ({
    create,
    update,
    getById,
    getAll,
    count,
});
