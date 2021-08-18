import { dbHelpers, db } from '@cerpus/edlib-node-utils';

const table = 'consumerUsers';

const create = async (consumerUser) => {
    const [id] = await db(table).insert(consumerUser);
    return getById(id);
};

const getById = async (id) => db(table).select('*').where('id', id).first();
const getByConsumerAndUserId = async (consumerId, consumerUserId, userId) =>
    db(table)
        .select('*')
        .where('consumerId', consumerId)
        .where('consumerUserId', consumerUserId)
        .where('userId', userId)
        .first();

const update = (id, consumerUser) =>
    dbHelpers.updateId(table, id, consumerUser);

const getAllWithDeprecatedTenantId = async () =>
    db(table).select('*').whereNotNull('deprecatedTenantId');

export default () => ({
    create,
    update,
    getAllWithDeprecatedTenantId,
    getByConsumerAndUserId,
    getById,
});
