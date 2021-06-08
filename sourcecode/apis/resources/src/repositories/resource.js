import { v4 as uuidv4 } from 'uuid';
import knex, { dbHelpers } from '@cerpus/edlib-node-utils/services/db.js';

const table = 'resources';

const create = async (resource) => {
    try {
        return await dbHelpers.create(table, {
            id: uuidv4(),
            ...resource,
        });
    } catch (e) {
        if (!dbHelpers.isUniqueViolation(e)) {
            throw e;
        }
    }

    return getById(resource.id);
};

const update = (id, resource) =>
    dbHelpers.updateId(table, id, {
        ...resource,
        updatedAt: new Date(),
    });

const getById = async (id) => knex(table).select('*').where('id', id).first();
const getByIds = async (ids) => knex(table).select('*').whereIn('id', ids);

const getAllPaginated = async (offset, limit) =>
    knex(table)
        .select('*')
        .orderBy('createdAt', 'DESC')
        .offset(offset)
        .limit(limit);

const count = async () =>
    (await knex(table).count('*', { as: 'count' }).first()).count;

export default () => ({
    create,
    update,
    getById,
    getByIds,
    getAllPaginated,
    count,
});
