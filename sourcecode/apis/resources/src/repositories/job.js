import { db, dbHelpers } from '@cerpus/edlib-node-utils';
const table = 'jobs';

const create = async (job) => {
    const [id] = await db(table).insert(job);

    return getById(id);
};

const update = (id, job) => dbHelpers.updateId(table, id, job);
const getById = async (id) => db(table).select('*').where('id', id).first();
const getRunning = async (jobName) =>
    db(table).select('*').where('type', jobName).whereNull('doneAt').first();

export default () => ({
    create,
    update,
    getById,
    getRunning,
});
