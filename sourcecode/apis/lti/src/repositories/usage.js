import { v4 as uuidv4 } from 'uuid';
import { db, dbHelpers } from '@cerpus/edlib-node-utils';

const table = 'usages';

const create = async (usage) => {
    try {
        return await dbHelpers.create(table, {
            id: uuidv4(),
            ...usage,
        });
    } catch (e) {
        if (!dbHelpers.isUniqueViolation(e)) {
            throw e;
        }
    }

    return getById(usage.id);
};

const createManyOrIgnore = async (usages) => {
    await db(table).insert(usages).onConflict('id').ignore();
};

const update = (id, usage) => dbHelpers.updateId(table, id, usage);

const getById = async (id) => db(table).select('*').where('id', id).first();

const createOrUpdate = async (usage) => {
    const existing = await getById(usage.id);

    if (existing) {
        return update(usage.id, usage);
    }

    return create(usage);
};

export default () => ({
    createOrUpdate,
    createManyOrIgnore,
    create,
    update,
    getById,
});
