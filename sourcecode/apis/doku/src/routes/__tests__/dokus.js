import db from '@cerpus/edlib-node-utils/services/db.js';
import versionClient from '@cerpus/edlib-node-utils/apiClients/version/index.js';
import licenseClient from '@cerpus/edlib-node-utils/apiClients/license/index.js';
import coreInternalClient from '@cerpus/edlib-node-utils/apiClients/coreInternal/index.js';
import request from '../../tests/request.js';
import addContextToRequest from '../../middlewares/addContextToRequest.js';
import { buildRawContext } from '../../context';

jest.mock('@cerpus/edlib-node-utils/apiClients/version/index.js');
jest.mock('@cerpus/edlib-node-utils/apiClients/license/index.js');
jest.mock('@cerpus/edlib-node-utils/apiClients/coreInternal/index.js');
jest.mock('../../middlewares/addContextToRequest.js');

describe('Test endpoints', () => {
    const dokuCountToCreate = 15;
    beforeEach(async () => {
        await db.migrate.latest();
        const context = buildRawContext();

        const dokuTemplate = (index) => ({
            title: `index-${index}`,
            data: {},
            creatorId: 'user-id',
            isDraft: false,
            isPublic: true,
        });

        for (let x = 0; x < dokuCountToCreate; x++) {
            await context.db.doku.create(dokuTemplate(x));
        }

        addContextToRequest.mockImplementation((req, res, next) => {
            req.context = buildRawContext(req);
            next();
        });
    });
    afterEach(async () => {
        await db.migrate.rollback({}, true);
    });
    describe('Dokus', () => {
        it('Create doku', async () => {
            const dokuToCreate = {
                data: {},
                title: 'title',
            };
            const license = 'by';
            const userId = 'user-id';

            const createMock = jest.fn().mockResolvedValue(true);
            versionClient.mockImplementation(() => ({
                create: createMock,
            }));

            const dokuRegisterMock = jest.fn().mockResolvedValue(true);
            coreInternalClient.mockImplementation(() => ({
                doku: {
                    triggerIndexUpdate: dokuRegisterMock,
                },
            }));

            const getResourceLicensesMock = jest
                .fn()
                .mockResolvedValue(license);
            licenseClient.mockImplementation(() => ({
                getForResource: getResourceLicensesMock,
            }));

            const response = await (await request())
                .post(`/api/v1/users/${userId}/dokus`)
                .send(dokuToCreate);

            expect(response.statusCode).toBe(200);
            expect(response.body.data).toStrictEqual(dokuToCreate.data);
            expect(response.body.title).toBe(dokuToCreate.title);
            expect(response.body.title).toBe(dokuToCreate.title);
            expect(response.body.license).toBe(license);
            expect(response.body.creatorId).toBe(userId);
            expect(response.body.isDraft).toBeTruthy();
            expect(response.body.isPublic).toBeFalsy();
            expect(Object.keys(response.body).length).toBe(10);
        });
        describe('Create doku returns 422 on invalid request', () => {
            const invalidBodies = [
                {
                    data: 'not an object',
                    title: 'asdf',
                },
                {
                    data: {},
                    title: '',
                },
                {
                    data: 'not an object',
                    title: '',
                },
            ];

            for (let invalidBody of invalidBodies) {
                it(JSON.stringify(invalidBody), async () => {
                    const response = await (await request())
                        .post(`/api/v1/users/user-id/dokus`)
                        .send(invalidBody);

                    expect(response.statusCode).toBe(422);
                });
            }
        });
        describe('Get dokus', () => {
            let context = null;
            let getDokusSpy = null;
            let getDokusCountSpy = null;

            beforeEach(() => {
                context = buildRawContext({});
                getDokusSpy = jest.spyOn(context.db.doku, 'get');
                getDokusCountSpy = jest.spyOn(context.db.doku, 'getCount');
                addContextToRequest.mockImplementation((req, res, next) => {
                    req.context = context;
                    next();
                });
            });

            it('Test default values', async () => {
                const response = await (await request()).get(`/api/v1/dokus`);

                expect(response.statusCode).toBe(200);
                expect(getDokusCountSpy).toHaveBeenCalledTimes(1);
                expect(getDokusSpy).toHaveBeenCalledTimes(1);
                expect(getDokusSpy).toHaveBeenCalledWith(0, 10, [
                    { column: 'createdAt', order: 'desc' },
                ]);
                expect(response.body.pagination.limit).toBe(10);
                expect(response.body.pagination.offset).toBe(0);
                expect(response.body.pagination.totalCount).toBe(
                    dokuCountToCreate
                );
                expect(response.body.resources.length).toBe(10);
            });
            it('should parse limit correctly', async () => {
                const limit = 2;
                const response = await (await request())
                    .get(`/api/v1/dokus`)
                    .query({
                        limit,
                    });

                expect(response.statusCode).toBe(200);
                expect(getDokusCountSpy).toHaveBeenCalledTimes(1);
                expect(getDokusSpy).toHaveBeenCalledTimes(1);
                expect(getDokusSpy).toHaveBeenCalledWith(0, limit, [
                    { column: 'createdAt', order: 'desc' },
                ]);
                expect(response.body.pagination.limit).toBe(limit);
                expect(response.body.resources.length).toBe(limit);
            });
            it('should parse sort_by correctly', async () => {
                const order = 'asc(updatedAt), desc(createdAt)';
                const response = await (await request())
                    .get(`/api/v1/dokus`)
                    .query({
                        sort_by: order,
                    });

                expect(response.statusCode).toBe(200);
                expect(getDokusCountSpy).toHaveBeenCalledTimes(1);
                expect(getDokusSpy).toHaveBeenCalledTimes(1);
                expect(getDokusSpy).toHaveBeenCalledWith(0, 10, [
                    { column: 'updatedAt', order: 'asc' },
                    { column: 'createdAt', order: 'desc' },
                ]);
                expect(response.body.pagination.limit).toBe(10);
                expect(response.body.resources.length).toBe(10);
            });
            it('should throw on invalid sort_by query parameter', async () => {
                const order = 'something';
                const response = await (await request())
                    .get(`/api/v1/dokus`)
                    .query({
                        sort_by: order,
                    });

                expect(response.statusCode).toBe(422);
            });
            it('should override offset if bellow 0', async () => {
                const response = await (await request())
                    .get(`/api/v1/dokus`)
                    .query({
                        offset: -1,
                    });

                expect(response.statusCode).toBe(200);
                expect(response.body.pagination.offset).toBe(0);
            });
            it('should override offset if not a number', async () => {
                const response = await (await request())
                    .get(`/api/v1/dokus`)
                    .query({
                        offset: 'asdf',
                    });

                expect(response.statusCode).toBe(200);
                expect(response.body.pagination.offset).toBe(0);
            });
            it('should override limit if bellow 0', async () => {
                const response = await (await request())
                    .get(`/api/v1/dokus`)
                    .query({
                        limit: -1,
                    });

                expect(response.statusCode).toBe(200);
                expect(response.body.pagination.limit).toBe(10);
            });
            it('should override limit if not a number', async () => {
                const response = await (await request())
                    .get(`/api/v1/dokus`)
                    .query({
                        limit: 'asdf',
                    });

                expect(response.statusCode).toBe(200);
                expect(response.body.pagination.limit).toBe(10);
            });
        });
    });
});
