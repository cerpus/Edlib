import { db, dbHelpers } from '@cerpus/edlib-node-utils';
import DataLoader from 'dataloader';
import { v4 as v4Uuid } from 'uuid';

const table = 'dokus';

const intToBooleanParser = (fields) => {
    const parser = (dbData) => {
        if (!dbData) {
            return dbData;
        }

        if (Array.isArray(dbData)) {
            return dbData.map((dbData) => parser(dbData));
        }

        return fields.reduce((dbData, field) => {
            dbData[field] = Boolean(dbData[field]);
            return dbData;
        }, dbData);
    };

    return (...args) => parser(...args);
};

const intToBoolean = intToBooleanParser(['isPublic', 'isDraft']);

const formatDokuOut = (...args) => {
    const formater = (dbData) => {
        if (!dbData) {
            return dbData;
        }

        if (Array.isArray(dbData)) {
            return dbData.map((dbData) => formater(dbData));
        }

        return intToBoolean({
            ...dbData,
            data: JSON.parse(dbData.data),
        });
    };

    return formater(...args);
};

const formatDokuIn = (...args) => {
    const formater = (doku) => {
        if (!doku) {
            return doku;
        }

        if (Array.isArray(doku)) {
            return doku.map((doku) => formater(doku));
        }

        if (doku.data) {
            doku.data = JSON.stringify(doku.data);
        }

        return doku;
    };

    return formater(...args);
};

const create = async (doku) => {
    delete doku.createdAt;
    delete doku.updatedAt;

    return formatDokuOut(
        await dbHelpers.create(
            table,
            formatDokuIn({
                ...doku,
                id: v4Uuid(),
            })
        )
    );
};

const update = async (id, doku) =>
    formatDokuOut(
        await dbHelpers.updateId(
            table,
            id,
            formatDokuIn({ ...doku, updatedAt: new Date() })
        )
    );

const get = async (offset, limit, orderBy) =>
    formatDokuOut(
        await db(table).select('*').orderBy(orderBy).offset(offset).limit(limit)
    );

const getCount = async () => {
    const [{ count }] = await db(table).count('* as count');

    return parseInt(count);
};

const getById = () =>
    new DataLoader(async (ids) => {
        const dokus = await db(table).select('*').whereIn('id', ids);
        return formatDokuOut(
            ids.map((id) => dokus.find((doku) => doku.id === id))
        );
    });

export default () => ({
    create,
    update,
    get,
    getCount,
    getById: getById(),
});
