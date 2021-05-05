const recommendations = jest.requireActual('../recommendations.js');

const mockAxios = async (options) => {
    if (options.url === '/v2/recommendations' && options.method === 'POST') {
        return {
            data: {
                offset: 0,
                limit: 10,
                approxTotal: 1,
                resources: [
                    {
                        className: 'ContentAuthorResourceInfo',
                        uuid: '0bf7ba3c-8239-43fd-b4df-6a7e57e642f4',
                        name: 'qs',
                        resourceType: 'H5P_RESOURCE',
                        created: '2020-02-05T13:41:43.470Z',
                        externalId: '3187a654-9cc3-4cdf-839c-0448c254a1a6',
                        contentAuthorType: 'QuestionSet',
                        gameType: null,
                        maxScore: null,
                        resourceCapabilities: ['edit'],
                    },
                ],
            },
        };
    }

    throw {
        service: 'core-axios',
        response: {
            status: 404,
            data: {
                code: 'NOT_FOUND',
            },
        },
    };
};

export default () => recommendations.default(mockAxios);
