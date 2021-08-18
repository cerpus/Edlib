import { dbHelpers, db } from '@cerpus/edlib-node-utils';

const table = 'users';

const create = async (user) => dbHelpers.create(table, user);
const update = (id, user) => dbHelpers.updateId(table, id, user);

const getById = async (id) => db(table).select('*').where('id', id).first();
const getByEmails = async (emails) =>
    db(table).select('*').whereIn('email', emails);

export default () => ({
    create,
    update,
    getById,
    getByEmails,
});
