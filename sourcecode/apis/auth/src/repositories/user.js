import knex, { dbHelpers } from '@cerpus/edlib-node-utils/services/db.js';

const table = 'users';

const create = async (user) => dbHelpers.create(table, user);
const update = (id, user) => dbHelpers.updateId(table, id, user);

const getById = async (id) => knex(table).select('*').where('id', id).first();

export default () => ({
    create,
    update,
    getById,
});
