import Joi from '@hapi/joi';
import {
    validateJoi,
    NotFoundException,
    validationExceptionError,
    ValidationException,
    UnauthorizedException,
    constants,
} from '@cerpus/edlib-node-utils';
import moment from 'moment';

const dokuValidationCreate = Joi.object().keys({
    data: Joi.object().required(),
    title: Joi.string().min(1).required(),
});

const dokuValidationUpdate = Joi.object().keys({
    data: Joi.object(),
    title: Joi.string().min(1),
    license: Joi.string().min(1),
    isPublic: Joi.boolean(),
});

const getDokuAndCheckPermission = async (req) => {
    let doku = await req.context.db.doku.getById.load(req.params.dokuId);

    if (!doku) {
        throw new NotFoundException('doku');
    }

    if (doku.creatorId !== req.params.userId) {
        throw new UnauthorizedException();
    }

    return doku;
};

const create = async (context, doku, parentId = null) => {
    const dbDoku = await context.db.doku.create({
        ...doku,
        editAllowedUntil: moment().add(15, 'minutes').toDate(),
    });

    await context.services.version.create(
        parentId
            ? constants.versionPurposes.UPDATE
            : constants.versionPurposes.CREATE,
        constants.externalSystemNames.DOKU,
        dbDoku.id,
        parentId
    );

    return dbDoku;
};

const withLicense = async (context, doku) => {
    if (doku.license) {
        return doku;
    }

    let license = null;
    try {
        license = await context.services.license.getForResource(
            constants.externalSystemNames.DOKU,
            doku.id
        );
    } catch (e) {
        if (!(e instanceof NotFoundException)) {
            throw e;
        }
    }

    return {
        ...doku,
        license,
    };
};

export default {
    publish: async (req, res, next) => {
        let doku = await getDokuAndCheckPermission(req);

        return await withLicense(
            req.context,
            await req.context.db.doku.update(doku.id, {
                isDraft: false,
            })
        );
    },
    unpublish: async (req, res, next) => {
        let doku = await getDokuAndCheckPermission(req);

        return await withLicense(
            req.context,
            await req.context.db.doku.update(doku.id, {
                isDraft: true,
            })
        );
    },
    create: async (req, res, next) => {
        const body = validateJoi(req.body, dokuValidationCreate);

        const doku = await withLicense(
            req.context,
            await create(req.context, {
                data: body.data,
                title: body.title,
                creatorId: req.params.userId,
            })
        );

        await req.context.services.coreInternal.doku.triggerIndexUpdate(
            doku.id
        );

        return doku;
    },
    update: async (req, res, next) => {
        const {
            license,
            isPublic,
            ...fieldsTriggeringNewVersion
        } = validateJoi(req.body, dokuValidationUpdate);

        let doku = await getDokuAndCheckPermission(req);

        const doesDokuTableHaveChanges =
            Object.keys(fieldsTriggeringNewVersion).length !== 0;

        const shouldCreateNewVersion =
            !doku.isDraft && doesDokuTableHaveChanges;

        if (shouldCreateNewVersion) {
            doku = await create(
                req.context,
                {
                    ...doku,
                    ...fieldsTriggeringNewVersion,
                    isPublic,
                    isDraft: true,
                },
                doku.id
            );
        } else {
            doku = await req.context.db.doku.update(req.params.dokuId, {
                ...fieldsTriggeringNewVersion,
                isPublic,
                editAllowedUntil: moment().add(5, 'minutes').toDate(),
            });
        }

        if (license) {
            await req.context.services.license.set(
                constants.externalSystemNames.DOKU,
                doku.id,
                license
            );
        }

        if (shouldCreateNewVersion || isPublic) {
            await req.context.services.coreInternal.doku.triggerIndexUpdate(
                doku.id
            );
        }

        return await withLicense(req.context, doku);
    },
    get: async (req, res, next) => {
        const doku = await req.context.db.doku.getById.load(req.params.dokuId);

        if (!doku) {
            throw new NotFoundException('doku');
        }

        delete doku.license;

        return await withLicense(req.context, doku);
    },
    getForUser: async (req, res, next) => {
        return await withLicense(
            req.context,
            await getDokuAndCheckPermission(req)
        );
    },
    getAll: async (req, res, next) => {
        let orderBy = [{ column: 'createdAt', order: 'desc' }];
        let limit = 10;
        let offset = 0;

        if (req.query.limit && !isNaN(req.query.limit)) {
            limit = parseInt(req.query.limit);
        }

        if (limit < 1) {
            limit = 10;
        }

        if (req.query.offset && !isNaN(req.query.offset)) {
            offset = parseInt(req.query.offset);
        }

        if (offset < 0) {
            offset = 0;
        }

        if (req.query.sort_by) {
            orderBy = req.query.sort_by
                .trim()
                .split(',')
                .map((f) => {
                    const result = /^(.*?)\((.*?)\)$/.exec(f.trim());

                    if (!result || result.length !== 3) {
                        throw new ValidationException(
                            validationExceptionError(
                                'sort_by',
                                'query',
                                'invalid sort_by query parameter. Must be in the format "desc(createdAt),asc(updatedAt)"'
                            )
                        );
                    }

                    if (['asc', 'desc'].indexOf(result[1]) === -1) {
                        throw new ValidationException(
                            validationExceptionError(
                                'sort_by',
                                'query',
                                'Invalid sort_by query parameter. Possible sort order are desc and ascS'
                            )
                        );
                    }

                    return {
                        column: result[2],
                        order: result[1],
                    };
                });
        }

        return {
            pagination: {
                limit,
                offset,
                totalCount: await req.context.db.doku.getCount(),
            },
            resources: await req.context.db.doku.get(offset, limit, orderBy),
        };
    },
};
