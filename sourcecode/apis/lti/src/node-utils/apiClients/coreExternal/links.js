export default (core) => {
    const create = async (resourceId) => {
        return (
            await core({
                url: `/v2/resource/${resourceId}/links`,
                method: 'POST',
            })
        ).data;
    };

    const createFromUrl = async (url) => {
        return (
            await core({
                url: `/v1/contentbyurl`,
                method: 'POST',
                params: {
                    url,
                },
            })
        ).data;
    };

    return {
        create,
        createFromUrl,
    };
};
