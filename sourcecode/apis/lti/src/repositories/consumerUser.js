import knex, { dbHelpers } from '@cerpus/edlib-node-utils/services/db.js';

const table = 'consumerUsers';

const create = async (consumerUser) => {
    const [id] = await knex(table).insert(consumerUser);
    return getById(id);
};

const getById = async (id) => knex(table).select('*').where('id', id).first();
const getByConsumerAndUserId = async (consumerId, consumerUserId, userId) =>
    knex(table)
        .select('*')
        .where('consumerId', consumerId)
        .where('consumerUserId', consumerUserId)
        .where('userId', userId)
        .first();

const update = (id, consumerUser) =>
    dbHelpers.updateId(table, id, consumerUser);

const getAllWithDeprecatedTenantId = async () =>
    knex(table).select('*').whereNotNull('deprecatedTenantId');

export default () => ({
    create,
    update,
    getAllWithDeprecatedTenantId,
    getByConsumerAndUserId,
    getById,
});
