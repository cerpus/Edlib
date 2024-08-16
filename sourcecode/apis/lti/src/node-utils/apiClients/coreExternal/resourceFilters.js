export default (core) => {
    const getContentAuthorTypes = async () => {
        return (
            await core({
                url: `/v1/contentauthortypes`,
                method: 'GET',
            })
        ).data;
    };

    const getLicenses = async () => {
        return (
            await core({
                url: `/v1/licenses`,
                method: 'GET',
            })
        ).data;
    };

    const getSources = async () => {
        return (
            await core({
                url: `/v1/ltitools/providers/all`,
                method: 'GET',
            })
        ).data;
    };

    const getMostPopularTags = async (count) => {
        return (
            await core({
                url: `/v1/tags/mostused/eng`,
                method: 'GET',
                params: {
                    count,
                },
            })
        ).data;
    };

    const searchTags = async (searchString, count) => {
        return (
            await core({
                url: `/v1/tags/search/eng?pattern=${searchString}*`,
                method: 'GET',
                params: {
                    count,
                },
            })
        ).data;
    };

    return {
        getContentAuthorTypes,
        getLicenses,
        getSources,
        getMostPopularTags,
        searchTags,
    };
};
