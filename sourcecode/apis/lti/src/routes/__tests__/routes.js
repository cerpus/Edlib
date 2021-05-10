import db from '@cerpus/edlib-node-utils/services/db.js';
import coreInternal from '@cerpus/edlib-node-utils/apiClients/coreInternal/index.js';
import request from '../../tests/request.js';

jest.mock('@cerpus/edlib-node-utils/apiClients/coreInternal/index.js');

describe('Test endpoints', () => {
    beforeEach(async () => {
        await db.migrate.latest();
    });
    afterEach(async () => {
        await db.migrate.rollback({}, true);
    });
    describe('Get mapping from EdLib ID', () => {
        const resourceData = {
            uuid: '28c048d9-e58c-412d-8b90-4f9c0349460f',
            contentAuthorId: '4',
            name: 'fdas',
            description: null,
            links: ['7c33f915-7f5a-4182-b16c-c90d361a9153'],
            systemResource: false,
            created: '2020-01-15T13:45:23.390Z',
            softDeleted: false,
            externalSystemName: 'contentAuthor',
            externalSystemId: '4',
        };
        afterEach(() => {
            coreInternal.mockReset();
        });
        test('Check if returned info is correct and stored to DB', async () => {
            const structureMock = jest.fn().mockResolvedValue(resourceData);
            coreInternal.mockImplementation(() => ({
                resource: {
                    structure: structureMock,
                },
            }));

            const response = await request((c) =>
                c.get(`/v1/edlib/${resourceData.uuid}`)
            );

            expect(structureMock).toBeCalledTimes(1);
            expect(response.statusCode).toBe(200);
            expect(Object.keys(response.body).length).toBe(4);
            expect(response.body.id).toBe(resourceData.uuid);
            expect(response.body.externalSystemName).toBe(
                resourceData.externalSystemName
            );
            expect(response.body.externalSystemId).toBe(
                resourceData.externalSystemId
            );
            expect(response.body.createdAt).toBeDefined();

            const rows = await db('ids');

            expect(rows.length).toBe(1);
            expect(Object.keys(rows[0]).length).toBe(4);
            expect(rows[0].id).toBe(resourceData.uuid);
            expect(rows[0].externalSystemName).toBe(
                resourceData.externalSystemName
            );
            expect(rows[0].externalSystemId).toBe(
                resourceData.externalSystemId
            );
            expect(rows[0].createdAt).toBeDefined();
        });

        test('Check if returned info is correct and loaded to DB', async () => {
            const structureMock = jest.fn().mockResolvedValue(resourceData);
            coreInternal.mockImplementation(() => ({
                resource: {
                    structure: structureMock,
                },
            }));

            await request((c) => c.get(`/v1/edlib/${resourceData.uuid}`));

            const response = await request((c) =>
                c.get(`/v1/edlib/${resourceData.uuid}`)
            );

            expect(structureMock).toBeCalledTimes(1);
            expect(response.statusCode).toBe(200);
            expect(Object.keys(response.body).length).toBe(4);
            expect(response.body.id).toBe(resourceData.uuid);
            expect(response.body.externalSystemName).toBe(
                resourceData.externalSystemName
            );
            expect(response.body.externalSystemId).toBe(
                resourceData.externalSystemId
            );
            expect(response.body.createdAt).toBeDefined();
        });

        test('Check if unknown id throws not found', async () => {
            const structureMock = jest.fn().mockResolvedValue(null);
            coreInternal.mockImplementation(() => ({
                resource: {
                    structure: structureMock,
                },
            }));

            await request((c) => c.get(`/v1/edlib/${resourceData.uuid}`));

            const response = await request((c) =>
                c.get(`/v1/edlib/${resourceData.uuid}`)
            );

            expect(response.statusCode).toBe(404);
        });
    });
    describe('Get mapping from external Id', () => {
        const resourceData = {
            className: 'ContentAuthorResourceInfo',
            uuid: 'd22df4fe-3d9f-4fb0-8d65-68def2718a88',
            name: 'cc0',
            resourceType: 'H5P_RESOURCE',
            created: '2020-10-22T08:48:57.829Z',
            externalId: '219',
            contentAuthorType: 'H5P.CoursePresentation',
            gameType: null,
            maxScore: 0,
            published: true,
            licenses: ['CC0'],
            externalSystemName: 'contentAuthor',
            resourceCapabilities: ['edit', 'view'],
        };
        afterEach(() => {
            coreInternal.mockReset();
        });
        test('Check if returned info is correct and stored to DB', async () => {
            const fromExternalIdInfoMock = jest
                .fn()
                .mockResolvedValue(resourceData);
            coreInternal.mockImplementation(() => ({
                resource: {
                    fromExternalIdInfo: fromExternalIdInfoMock,
                },
            }));

            const response = await request((c) =>
                c.get(
                    `/v1/external/${resourceData.externalSystemName}/${resourceData.externalId}`
                )
            );

            expect(fromExternalIdInfoMock).toBeCalledTimes(1);
            expect(response.statusCode).toBe(200);
            expect(Object.keys(response.body).length).toBe(4);
            expect(response.body.id).toBe(resourceData.uuid);
            expect(response.body.externalSystemName).toBe(
                resourceData.externalSystemName
            );
            expect(response.body.externalSystemId).toBe(
                resourceData.externalId
            );
            expect(response.body.createdAt).toBeDefined();

            const rows = await db('ids');

            expect(rows.length).toBe(1);
            expect(Object.keys(rows[0]).length).toBe(4);
            expect(rows[0].id).toBe(resourceData.uuid);
            expect(rows[0].externalSystemName).toBe(
                resourceData.externalSystemName
            );
            expect(rows[0].externalSystemId).toBe(resourceData.externalId);
            expect(rows[0].createdAt).toBeDefined();
        });

        test('Check if returned info is correct and loaded to DB', async () => {
            const fromExternalIdInfoMock = jest
                .fn()
                .mockResolvedValue(resourceData);
            coreInternal.mockImplementation(() => ({
                resource: {
                    fromExternalIdInfo: fromExternalIdInfoMock,
                },
            }));

            await request((c) =>
                c.get(
                    `/v1/external/${resourceData.externalSystemName}/${resourceData.externalId}`
                )
            );

            const response = await request((c) =>
                c.get(
                    `/v1/external/${resourceData.externalSystemName}/${resourceData.externalId}`
                )
            );

            expect(fromExternalIdInfoMock).toBeCalledTimes(1);
            expect(response.statusCode).toBe(200);
            expect(Object.keys(response.body).length).toBe(4);
            expect(response.body.id).toBe(resourceData.uuid);
            expect(response.body.externalSystemName).toBe(
                resourceData.externalSystemName
            );
            expect(response.body.externalSystemId).toBe(
                resourceData.externalId
            );
            expect(response.body.createdAt).toBeDefined();
        });

        test('Check if unknown id throws not found', async () => {
            const fromExternalIdInfoMock = jest.fn().mockResolvedValue(null);

            coreInternal.mockImplementation(() => ({
                resource: {
                    fromExternalIdInfo: fromExternalIdInfoMock,
                },
            }));

            const response = await request((c) =>
                c.get(
                    `/v1/external/${resourceData.externalSystemName}/${resourceData.externalId}`
                )
            );

            expect(response.statusCode).toBe(404);
        });
    });
    describe('Health', () => {
        test('Check if returned http code is 200 when everything is up', async () => {
            const response = await request((c) =>
                c.get(`/resources/_ah/health?probe=readiness`)
            );

            expect(response.statusCode).toBe(200);
        });

        test('Check if returned http code is 503 when db is not migrated', async () => {
            await db.migrate.rollback({}, true);

            const response = await request((c) =>
                c.get(`/resources/_ah/health?probe=readiness`)
            );

            expect(response.statusCode).toBe(503);
        });

        test('Check if returned http code is 503 when wrong probe is passed', async () => {
            const response = await request((c) =>
                c.get(`/resources/_ah/health?probe=liveness`)
            );

            expect(response.statusCode).toBe(503);
        });

        test('Check if returned http code is 503 when no probe is passed', async () => {
            const response = await request((c) =>
                c.get(`/resources/_ah/health`)
            );

            expect(response.statusCode).toBe(503);
        });
    });
    describe('Home', () => {
        test('Home returns http 200', async () => {
            const response = await request((c) => c.get(`/`));

            expect(response.statusCode).toBe(200);
        });
    });
});
