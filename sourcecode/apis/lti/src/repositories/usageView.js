import { v4 as uuidv4 } from 'uuid';
import { dbHelpers, db } from '../node-utils/index.js';

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

const createManyOrIgnore = async (usages) => {
    await db(table).insert(usages).onConflict('id').ignore();
};

const update = (id, usage) => dbHelpers.updateId(table, id, usage);

const getById = async (id) => db(table).select('*').where('id', id).first();
const getPaginatedWithResourceInfo = async (offset, limit) =>
    db
        .select('usageViews.*')
        .select('u.resourceId')
        .select('u.resourceVersionId')
        .from(db(table).offset(offset).limit(limit).as('usageViews'))
        .join('usages as u', 'u.id', 'usageViews.usageId');

const createOrUpdate = async (usageView) => {
    const existing = await getById(usageView.id);

    if (existing) {
        return update(usageView.id, usageView);
    }

    return create(usageView);
};

const count = async () =>
    (await db(table).count('*', { as: 'count' }).first()).count;

export default () => ({
    create,
    createManyOrIgnore,
    update,
    createOrUpdate,
    getById,
    getPaginatedWithResourceInfo,
    count,
});
