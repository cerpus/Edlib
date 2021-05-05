export default (core) => {
    const create = async (authorizationJwt, type, ltiExtraParameters) => {
        return (
            await core({
                url: `/v2/contentauthor/create${type ? `/${type}` : ''}`,
                method: 'POST',
                authorizationJwt,
                data: ltiExtraParameters,
            })
        ).data;
    };

    const edit = async (
        authorizationJwt,
        resourceId,
        translateToLanguage,
        launchPresentationLocale
    ) => {
        return (
            await core({
                url: `/v2/contentauthor/edit/${resourceId}`,
                method: 'POST',
                authorizationJwt,
                data: {
                    translateLanguage: translateToLanguage || undefined,
                    launchPresentationLocale,
                },
            })
        ).data;
    };

    return {
        create,
        edit,
    };
};
