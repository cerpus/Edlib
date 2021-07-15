import request from '../../tests/request.js';
import { UnauthorizedException, middlewares } from '@cerpus/edlib-node-utils';

jest.mock('@cerpus/edlib-node-utils', () => {
    const actual = jest.requireActual('@cerpus/edlib-node-utils');
    return {
        ...actual,
        apiClients: {
            ...actual.apiClients,
            coreInternal: () => ({
                resource: {
                    info: async () => ({
                        data: {
                            domain: null,
                            author: null,
                            language: 'en',
                            h5pVersion: '1.20',
                            customFieldAuthorResponseTimeMs: 2738,
                            copyrightEndpointResponseTimeMs: 145,
                            infoEndpointResponseTimeMs: 2734,
                        },
                    }),
                },
            }),
            coreExternal: () => ({
                recommendations: {
                    get: async () => ({
                        pagination: {
                            offset: 0,
                            limit: 1,
                            totalCount: 1,
                        },
                        data: [
                            {
                                className: 'ContentAuthorResourceInfo',
                                uuid: '8daaef14-ce74-4200-b50e-7741f4aa0c43',
                                name: 'EDL-597 v1',
                                resourceType: 'H5P_RESOURCE',
                                created: '2020-07-02T11:28:13.931Z',
                                externalId: '98',
                                contentAuthorType: 'H5P.CoursePresentation',
                                gameType: null,
                                maxScore: 0,
                                published: true,
                                licenses: ['by'],
                                externalSystemName: 'contentAuthor',
                                resourceCapabilities: [],
                            },
                        ],
                    }),
                },
                resource: {
                    info: async () => ({
                        data: {
                            domain: null,
                            author: null,
                            language: 'en',
                            h5pVersion: '1.20',
                            customFieldAuthorResponseTimeMs: 2738,
                            copyrightEndpointResponseTimeMs: 145,
                            infoEndpointResponseTimeMs: 2734,
                        },
                    }),
                },
            }),
        },
        middlewares: {
            ...actual.middlewares,
            isUserAuthenticated: jest.fn(),
        },
    };
});

describe('Test endpoints', () => {
    describe('Get resources', () => {
        test('Get unauthorized if no token', async () => {
            middlewares.isUserAuthenticated.mockImplementation(
                (req, res, next) => next(new UnauthorizedException())
            );
            const response = await request((c) =>
                c.post('/resources/v1/resources')
            );

            expect(response.statusCode).toBe(401);
        });

        test('Get resources returns expected response', async () => {
            middlewares.isUserAuthenticated.mockImplementation(
                (req, res, next) => {
                    next();
                }
            );

            const response = await request((c) =>
                c.post('/resources/v1/resources')
            );

            expect(response.statusCode).toBe(200);
            expect(response.body.pagination).toBeDefined();
            expect(response.body.pagination.offset).toBeDefined();
            expect(response.body.pagination.limit).toBeDefined();
            expect(response.body.pagination.totalCount).toBeDefined();

            expect(Array.isArray(response.body.data)).toBe(true);

            const resource = response.body.data.find(
                (resource) => resource.externalId === '98'
            );

            expect(resource).toBeTruthy();
            expect(resource.license).toBeTruthy();
            expect(resource.uuid).toBeUndefined();
        });
    });
});
