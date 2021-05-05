export default (core) => {
    const get = async (resourceId) => {
        return (
            await core({
                url: `/v1/preview/${resourceId}`,
                method: 'GET',
            })
        ).data;
    };

    return {
        get,
    };
};
