import request from '../../tests/request.js';
import {
    apiClients,
    UnauthorizedException,
    middlewares,
} from '@cerpus/edlib-node-utils';

jest.mock('@cerpus/edlib-node-utils', () => {
    const actual = jest.requireActual('@cerpus/edlib-node-utils');
    return {
        ...actual,
        apiClients: {
            ...actual.apiClients,
            id: jest.fn(),
            version: jest.fn(),
        },
        middlewares: {
            ...actual.middlewares,
            isUserAuthenticated: jest.fn(),
        },
    };
});

describe('Test endpoints', () => {
    describe('Get resource versions', () => {
        test('Get unauthorized if no token', async () => {
            middlewares.isUserAuthenticated.mockImplementation(
                (req, res, next) => next(new UnauthorizedException())
            );
            const response = await request((c) =>
                c.get('/resources/v1/resources/any-id/versions')
            );

            expect(response.statusCode).toBe(401);
        });

        // test('Get resources returns expected response', async () => {
        //     middlewares.isUserAuthenticated.mockImplementation(
        //         (req, res, next) => {
        //             next();
        //         }
        //     );
        //
        //     const idMapping = {
        //         id: 'resource-id',
        //         externalSystemId: 'external-id',
        //         externalSystemName: 'doku',
        //     };
        //
        //     const idMapping2 = {
        //         id: 'resource-id-2',
        //         externalSystemId: '27dad1ba-7b07-4ab0-a1e4-088f0428cae5',
        //         externalSystemName: 'doku',
        //     };
        //
        //     const versionResource1 = {
        //         externalSystem: 'ContentAuthor',
        //         externalReference: idMapping.externalSystemId,
        //         createdAt: 1605706013580,
        //     };
        //
        //     const versionResource2 = {
        //         externalSystem: 'ContentAuthor',
        //         externalReference: idMapping2.externalSystemId,
        //         createdAt: 1605705668305,
        //     };
        //
        //     const structure1 = {
        //         name: 'name1',
        //         created: 1,
        //     };
        //
        //     const structure2 = {
        //         name: 'name2',
        //         created: 2,
        //     };
        //
        //     const getForResourceMock = jest.fn().mockResolvedValueOnce({
        //         ...versionResource1,
        //         parent: versionResource2,
        //     });
        //
        //     apiClients.version.mockImplementation(() => ({
        //         getForResource: getForResourceMock,
        //     }));
        //
        //     const getForIdMock = jest.fn().mockResolvedValueOnce(idMapping);
        //     const getForExternalMock = jest
        //         .fn()
        //         .mockResolvedValueOnce(idMapping)
        //         .mockResolvedValueOnce(idMapping2);
        //
        //     apiClients.id.mockImplementation(() => ({
        //         getForId: getForIdMock,
        //         getForExternal: getForExternalMock,
        //     }));
        //
        //     const structureMock = jest
        //         .fn()
        //         .mockResolvedValueOnce(structure1)
        //         .mockResolvedValueOnce(structure2);
        //
        //     const response = await request((c) =>
        //         c
        //             .get(`/resources/v1/resources/${idMapping.id}/versions`)
        //             .set({ Authorization: 'Bearer test' })
        //     );
        //
        //     expect(response.statusCode).toBe(200);
        //     expect(getForIdMock).toBeCalledTimes(1);
        //     expect(getForIdMock).toBeCalledWith(idMapping.id);
        //     expect(getForExternalMock).toBeCalledTimes(2);
        //     expect(getForExternalMock.mock.calls).toEqual([
        //         [
        //             versionResource1.externalSystem,
        //             versionResource1.externalReference,
        //         ],
        //         [
        //             versionResource2.externalSystem,
        //             versionResource2.externalReference,
        //         ],
        //     ]);
        //     expect(structureMock.mock.calls).toEqual([
        //         [idMapping.id],
        //         [idMapping2.id],
        //     ]);
        //     expect(response.body).toEqual([
        //         {
        //             edlibId: idMapping.id,
        //             name: structure1.name,
        //             createdAt: structure1.created,
        //         },
        //         {
        //             edlibId: idMapping2.id,
        //             name: structure2.name,
        //             createdAt: structure2.created,
        //         },
        //     ]);
        // });
    });
});
