import { dbHelpers, db } from '@cerpus/edlib-node-utils';
import moment from 'moment';

const table = 'jwksKeys';

const create = async (jwksKey) => {
    const [id] = await db(table).insert(jwksKey);

    return getById(id);
};
const update = (id, jwksKey) => dbHelpers.updateId(table, id, jwksKey);

const getById = async (id) => db(table).select('*').where('id', id).first();
const getAllActive = async () => {
    return db(table)
        .select('*')
        .where(function () {
            this.whereNull('expiresAt').orWhere(
                'expiresAt',
                '<',
                moment().toISOString()
            );
        });
};

export default () => ({
    create,
    update,
    getById,
    getAllActive,
});
