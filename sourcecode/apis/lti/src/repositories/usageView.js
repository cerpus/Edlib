import { v4 as uuidv4 } from 'uuid';
import knex, {
    dbHelpers,
} from '@cerpus/edlib-node-utils/services/db.js';

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

const getById = async (id) => knex(table).select('*').where('id', id).first();

export default () => ({
    create,
    getById,
});
