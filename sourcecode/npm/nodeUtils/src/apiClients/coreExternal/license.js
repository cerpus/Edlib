export default (core) => {
    const getAll = async () => {
        return (
            await core({
                url: `/v1/licenses`,
                method: 'GET',
            })
        ).data;
    };

    return {
        getAll,
    };
};
