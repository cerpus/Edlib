import { dbHelpers, db } from '@cerpus/edlib-node-utils';

const table = 'users';

const create = async (user) => dbHelpers.create(table, user);
const update = (id, user) => dbHelpers.updateId(table, id, user);

const getById = async (id) => db(table).select('*').where('id', id).first();
const getByIds = async (ids) => db(table).select('*').whereIn('id', ids);
const getByEmails = async (emails) =>
    db(table).select('*').whereIn('email', emails);
const updateUserIds = async (userIds) => {
    return db(table)
        .update({
            id: db.raw(`CASE id
                      ${userIds
                          .map((userId) =>
                              db
                                  .raw(`WHEN ? THEN ?`, [
                                      userId.from,
                                      userId.to,
                                  ])
                                  .toString()
                          )
                          .join('\n')}
                      END
    `),
        })
        .whereIn(
            'id',
            userIds.map((userId) => userId.from)
        );
};

export default () => ({
    create,
    update,
    getById,
    getByIds,
    getByEmails,
    updateUserIds,
});
