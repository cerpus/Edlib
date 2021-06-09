import { v4 as uuidv4 } from 'uuid';
import knex, { dbHelpers } from '@cerpus/edlib-node-utils/services/db.js';

const table = 'resourceVersions';

const format = (resourceVersion) => {
    if (resourceVersion.externalSystemName) {
        resourceVersion.externalSystemName = resourceVersion.externalSystemName.toLowerCase();
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

const getById = async (id) => knex(table).select('*').where('id', id).first();
const getByIds = async (ids) => knex(table).select('*').whereIn('id', ids);

const getByExternalId = async (systemName, id) =>
    knex(table)
        .select('*')
        .where('externalSystemName', systemName.toLowerCase())
        .where('externalSystemId', id)
        .first();

const getFirstFromExternalSytemReference = async (externalSystemReferences) => {
    const queryBeforeWhere = knex(table).select('*');

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
    knex(table)
        .select('*')
        .where('resourceId', resourceId)
        .orderBy('createdAt', 'DESC')
        .first();

const getLatestNonDraftResourceVersion = async (resourceId) =>
    knex(table)
        .select('*')
        .where('resourceId', resourceId)
        .orderBy('createdAt', 'DESC')
        .first();

const getLatestPublishedResourceVersion = async (resourceId) =>
    knex(table)
        .select('*')
        .where('resourceId', resourceId)
        .where('isPublished', true)
        .orderBy('createdAt', 'DESC')
        .first();

const getContentTypesForExternalSystemName = async (externalSystemName) =>
    knex(table)
        .distinct('contentType')
        .where('externalSystemName', externalSystemName);

const getAllPaginated = async (offset, limit) =>
    knex(table)
        .select('*')
        .orderBy('createdAt', 'ASC')
        .offset(offset)
        .limit(limit);

export default () => ({
    create,
    update,
    getById,
    getByIds,
    getByExternalId,
    getFirstFromExternalSytemReference,
    getLatestResourceVersion,
    getLatestPublishedResourceVersion,
    getContentTypesForExternalSystemName,
    getAllPaginated,
    getLatestNonDraftResourceVersion,
});
