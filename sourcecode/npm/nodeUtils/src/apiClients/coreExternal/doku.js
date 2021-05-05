export default (core) => {
    const getLaunchData = async (token) => {
        return (
            await core({
                url: `/v2/dokulaunch`,
                method: 'GET',
                params: {
                    token,
                },
            })
        ).data;
    };

    return {
        getLaunchData,
    };
};
