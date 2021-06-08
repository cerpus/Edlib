import knex, { dbHelpers } from '@cerpus/edlib-node-utils/services/db.js';

const table = 'jobs';

const create = async (job) => {
    const [id] = await knex(table).insert(job);

    return getById(id);
};

const update = (id, job) => dbHelpers.updateId(table, id, job);
const getById = async (id) => knex(table).select('*').where('id', id).first();
const getRunning = async (jobName) =>
    knex(table).select('*').where('type', jobName).whereNull('doneAt').first();

export default () => ({
    create,
    update,
    getById,
    getRunning,
});
