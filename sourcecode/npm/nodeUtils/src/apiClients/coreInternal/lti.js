export default (core) => {
    const getAllUsages = async (limit = 100, offset = 0) => {
        const response = await core({
            url: `/v1/lti-usages`,
            method: 'GET',
            params: {
                limit,
                offset,
            },
        });

        return response.data;
    };

    const getAllUsageViews = async (limit = 100, offset = 0) => {
        const response = await core({
            url: `/v1/lti-usage-views`,
            method: 'GET',
            params: {
                limit,
                offset,
            },
        });

        return response.data;
    };

    return {
        getAllUsages,
        getAllUsageViews,
    };
};
