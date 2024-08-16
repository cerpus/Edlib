export default (core) => {
    const get = async (body) => {
        const format = (response) => ({
            pagination: {
                offset: response.offset,
                limit: response.limit,
                totalCount: response.approxTotal,
            },
            data: response.resources,
        });

        return format(
            (
                await core({
                    url: '/v2/recommendations',
                    method: 'POST',
                    data: body,
                    headers: { 'x-environment': 'internal' },
                })
            ).data
        );
    };

    return {
        get,
    };
};
