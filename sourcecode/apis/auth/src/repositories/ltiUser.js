import { dbHelpers, db } from '@cerpus/edlib-node-utils';
import { v4 as uuidv4 } from 'uuid';

const table = 'ltiUsers';

const create = async (user) =>
    dbHelpers.create(table, {
        id: uuidv4(),
        ...user,
    });

const update = (id, user) => dbHelpers.updateId(table, id, user);
const getById = async (id) => db(table).select('*').where('id', id).first();
const getByLtiReference = async (clientId, deploymentId, externalId) =>
    db(table)
        .select('*')
        .where('clientId', clientId)
        .where('deploymentId', deploymentId)
        .where('externalId', externalId)
        .first();

export default () => ({
    getById,
    create,
    update,
    getByLtiReference,
});
