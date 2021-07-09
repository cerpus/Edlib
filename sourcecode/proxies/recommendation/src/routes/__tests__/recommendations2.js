import { middlewares } from '@cerpus/edlib-node-utils';
import request from '../../tests/request.js';

jest.mock('@cerpus/edlib-node-utils', () => {
    const actual = jest.requireActual('@cerpus/edlib-node-utils');
    return {
        ...actual,
        middlewares: {
            ...actual.middlewares,
            isUserAuthenticated: jest.fn(),
        },
        apiClients: {
            version: jest.fn(),
            license: jest.fn(),
            coreInternal: () => ({
                resource: {
                    fromExternalIdInfo: async () => ({
                        className: 'ContentAuthorResourceInfo',
                        uuid: '95dd43c9-04ac-44c2-ba07-836db2183400',
                        name: 'test course',
                        resourceType: 'H5P_RESOURCE',
                        created: '2020-11-04T11:37:04.436Z',
                        externalId: '234',
                        contentAuthorType: 'H5P.CoursePresentation',
                        gameType: null,
                        maxScore: 0,
                        published: true,
                        licenses: [],
                        externalSystemName: 'contentAuthor',
                        resourceCapabilities: ['edit', 'view'],
                    }),
                },
            }),
            id: jest.fn(),
            auth: jest.fn(),
            coreExternal: jest.fn(),
            edlibAuth: jest.fn(),
        },
    };
});

jest.mock('../../services/recommender/index.js', () => () => ({
    recommend: {
        getRecommendation: async () => ({
            recommendations: [
                {
                    id: 'h5p-234',
                    title: 'test course',
                    content: '',
                    description: '',
                    last_updated_at: 1603963301,
                    tags: [],
                    type: 'h5p.coursepresentation',
                    license: 'by',
                    rank: 16,
                    use_report_url:
                        'http://recommender-engine:8080/recommend/usage/0011399d-8a88-4760-8b21-a35692f5b027/16',
                },
            ],
        }),
    },
}));

describe('Test endpoints', () => {
    describe('Recommendations 2', () => {
        let isUserAuthenticated = middlewares.isUserAuthenticated;
        beforeEach(() => {
            middlewares.isUserAuthenticated = jest
                .fn()
                .mockImplementationOnce((req, res, next) => {
                    return next();
                });
        });
        test('Get recommendation returns correct content', async () => {
            const response = await request((c) =>
                c
                    .post('/recommendations/v1/recommendations')
                    .send({
                        searchString: 'test',
                    })
                    .set({ Authorization: 'Bearer test' })
            );

            expect(response.statusCode).toBe(200);
            expect(response.body.data).toBeDefined();

            expect(Array.isArray(response.body.data)).toBe(true);

            const resource = response.body.data.find(
                (resource) =>
                    resource.edlibId === '95dd43c9-04ac-44c2-ba07-836db2183400'
            );

            expect(resource).toBeTruthy();
            expect(resource.license).toBeTruthy();
        });
        afterEach(() => {
            middlewares.isUserAuthenticated = isUserAuthenticated;
        });
    });
});
