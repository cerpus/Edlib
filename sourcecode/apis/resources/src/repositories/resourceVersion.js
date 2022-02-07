import { v4 as uuidv4 } from 'uuid';
import { db, dbHelpers } from '@cerpus/edlib-node-utils';

const table = 'resourceVersions';

const format = (resourceVersion) => {
    if (resourceVersion.externalSystemName) {
        resourceVersion.externalSystemName =
            resourceVersion.externalSystemName.toLowerCase();
    }

    return resourceVersion;
};

const create = async (resourceVersion) => {
    try {
        return await dbHelpers.create(
            table,
            format({
                id: uuidv4(),
                ...resourceVersion,
            })
        );
    } catch (e) {
        if (!dbHelpers.isUniqueViolation(e)) {
            throw e;
        }
    }

    return getById(resourceVersion.id);
};

const update = (id, resourceVersion) =>
    dbHelpers.updateId(table, id, format(resourceVersion));

const getById = async (id) => db(table).select('*').where('id', id).first();
const getByIds = async (ids) => db(table).select('*').whereIn('id', ids);

const getByExternalId = async (systemName, id) =>
    db(table)
        .select('*')
        .where('externalSystemName', systemName.toLowerCase())
        .where('externalSystemId', id)
        .first();

const getFirstFromExternalSytemReference = async (externalSystemReferences) => {
    const queryBeforeWhere = db(table).select('*');

    externalSystemReferences.forEach(
        ({ externalSystemName, externalSystemId }) => {
            queryBeforeWhere.orWhere(function () {
                this.where(
                    'externalSystemName',
                    externalSystemName.toLowerCase()
                ).where('externalSystemId', externalSystemId);
            });
        }
    );

    return queryBeforeWhere.first();
};

const getLatestResourceVersion = async (resourceId) =>
    db(table)
        .select('*')
        .where('resourceId', resourceId)
        .orderBy('createdAt', 'DESC')
        .first();

const getLatestNonDraftResourceVersion = async (resourceId) =>
    db(table)
        .select('*')
        .where('resourceId', resourceId)
        .orderBy('createdAt', 'DESC')
        .first();

const getLatestPublishedResourceVersion = async (resourceId) =>
    db(table)
        .select('*')
        .where('resourceId', resourceId)
        .where('isPublished', true)
        .orderBy('createdAt', 'DESC')
        .first();

const getContentTypesForExternalSystemName = async (externalSystemName) =>
    db(table)
        .distinct('contentType')
        .where('externalSystemName', externalSystemName);

const getAllPaginated = async (offset, limit) =>
    db(table)
        .select('*')
        .orderBy('createdAt', 'ASC')
        .offset(offset)
        .limit(limit);

const count = async () =>
    (await db(table).count('*', { as: 'count' }).first()).count;

const getCountForOwners = async (ids) =>
    (
        await db(table)
            .count('*', { as: 'count' })
            .whereIn('ownerId', ids)
            .first()
    ).count;
const updateOwnerIds = async (userIds) => {
    return db(table)
        .update({
            ownerId: db.raw(`CASE ownerId
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
            'ownerId',
            userIds.map((userId) => userId.from)
        );
};

const remove = async (id) => db(table).where('id', id).del();

export default () => ({
    create,
    update,
    remove,
    getById,
    getByIds,
    getByExternalId,
    getFirstFromExternalSytemReference,
    getLatestResourceVersion,
    getLatestPublishedResourceVersion,
    getContentTypesForExternalSystemName,
    getAllPaginated,
    getLatestNonDraftResourceVersion,
    count,
    getCountForOwners,
    updateOwnerIds,
});
