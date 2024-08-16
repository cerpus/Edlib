import { dbHelpers, db } from '../node-utils/index.js';

const table = 'consumers';

const create = async (consumer) => {
    const [id] = await db(table).insert(consumer);
    return getById(id);
};
const update = (id, consumer) => dbHelpers.updateId(table, id, consumer);
const getById = async (id) => db(table).select('*').where('id', id).first();
const getByKey = async (key) => db(table).select('*').where('key', key).first();
const getAll = async () => db(table).select('*');

export default () => ({
    create,
    update,
    getById,
    getByKey,
    getAll,
});
