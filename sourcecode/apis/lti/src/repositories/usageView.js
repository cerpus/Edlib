import { v4 as uuidv4 } from 'uuid';
import { dbHelpers, db } from '@cerpus/edlib-node-utils';

const table = 'usageViews';

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

const update = (id, usage) => dbHelpers.updateId(table, id, usage);

const getById = async (id) => db(table).select('*').where('id', id).first();

const createOrUpdate = async (usageView) => {
    const existing = await getById(usageView.id);

    if (existing) {
        return update(usageView.id, usageView);
    }

    return create(usageView);
};

export default () => ({
    create,
    update,
    createOrUpdate,
    getById,
});
