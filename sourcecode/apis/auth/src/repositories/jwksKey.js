import knex, { dbHelpers } from '@cerpus/edlib-node-utils/services/db.js';
import moment from 'moment';

const table = 'jwksKeys';

const create = async (jwksKey) => {
    const [id] = await knex(table).insert(jwksKey);

    return getById(id);
};
const update = (id, jwksKey) => dbHelpers.updateId(table, id, jwksKey);

const getById = async (id) => knex(table).select('*').where('id', id).first();
const getAllActive = async () => {
    return knex(table)
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
