export default (core) => {
    const triggerIndexUpdate = async (dokuId) => {
        await core({
            url: `/v2/dokus/${dokuId}`,
            method: 'POST',
        });

        return true;
    };

    return {
        triggerIndexUpdate,
    };
};
