const recommend = jest.requireActual('../recommend.js');

const mockAxios = async (options) => {
    if (options.url === '/recommend' && options.method === 'POST') {
        return {
            data: {
                id: 'adb91cde-aa9c-45e0-b30d-711084636de2',
                recommendations: [
                    {
                        id: 'h5p-34',
                        title: 'test',
                        content: '',
                        description: '',
                        last_updated_at: 1588072468,
                        tags: [''],
                        type: 'h5p.coursepresentation',
                        license: 'by',
                        rank: 1,
                        use_report_url:
                            'http://recommender-engine.api/recommend/usage/adb91cde-aa9c-45e0-b30d-711084636de2/1',
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

export default () => recommend.default(mockAxios);
