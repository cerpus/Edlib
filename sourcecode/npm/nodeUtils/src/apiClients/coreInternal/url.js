export default (core) => {
    const getAllUrlResources = async (limit = 100, offset = 0) => {
        const response = await core({
            url: `/v1/url-resources`,
            method: 'GET',
            params: {
                limit,
                offset,
            },
        });

        return response.data;
    };

    return {
        getAllUrlResources,
    };
};
