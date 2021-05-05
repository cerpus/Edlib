const resource = jest.requireActual('../resource.js');

const mockAxios = async (options) => {
    if (
        options.url ===
            '/v2/resource/0bf7ba3c-8239-43fd-b4df-6a7e57e642f4/info' &&
        options.method === 'GET'
    ) {
        return {
            data: {
                domain: null,
                author: null,
                language: 'en',
                h5pVersion: '1.20',
                customFieldAuthorResponseTimeMs: 821,
                copyrightEndpointResponseTimeMs: 169,
                infoEndpointResponseTimeMs: 310,
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

export default () => resource.default(mockAxios);
