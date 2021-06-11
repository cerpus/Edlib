import { dbHelpers, db } from '@cerpus/edlib-node-utils';

const table = 'urls';

const create = async (url) => dbHelpers.create(table, url);
const update = (id, url) => dbHelpers.updateId(table, id, url);

const getById = async (id) => db(table).select('*').where('id', id).first();
const getAll = async (limit, offset) =>
    db(table).select('*').offset(offset).limit(limit);
const count = async () => db(table).count('*', { as: 'count' }).first();

export default () => ({
    create,
    update,
    getById,
    getAll,
    count,
});
