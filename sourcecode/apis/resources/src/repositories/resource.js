import { v4 as uuidv4 } from 'uuid';
import { db, dbHelpers } from '@cerpus/edlib-node-utils';

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

const getById = async (id) => db(table).select('*').where('id', id).first();
const getByIds = async (ids) => db(table).select('*').whereIn('id', ids);

const getAllPaginated = async (offset, limit) =>
    db(table)
        .select('*')
        .orderBy('createdAt', 'DESC')
        .offset(offset)
        .limit(limit);

const count = async () =>
    (await db(table).count('*', { as: 'count' }).first()).count;

const remove = async (id) => db(table).where('id', id).del();

export default () => ({
    create,
    update,
    remove,
    getById,
    getByIds,
    getAllPaginated,
    count,
});
