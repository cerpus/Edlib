import { v4 as uuidv4 } from 'uuid';
import { db, dbHelpers } from '@cerpus/edlib-node-utils';

const table = 'resourceGroups';

const create = async (resourceGroup) => {
    try {
        return await dbHelpers.create(table, {
            id: uuidv4(),
            ...resourceGroup,
        });
    } catch (e) {
        if (!dbHelpers.isUniqueViolation(e)) {
            throw e;
        }
    }

    return getById(resourceGroup.id);
};

const update = (id, resourceGroup) =>
    dbHelpers.updateId(table, id, {
        ...resourceGroup,
        updatedAt: new Date(),
    });

const getById = async (id) => db(table).select('*').where('id', id).first();

export default () => ({
    create,
    update,
    getById,
});
