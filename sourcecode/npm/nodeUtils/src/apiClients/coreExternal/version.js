export default (core) => {
    const getForResource = async (resourceId) => {
        const resourceFormat = (resource) => ({
            edlibId: resource.uuid,
            name: resource.name,
        });

        const format = (data, versions = []) => {
            const newVersions = [...versions, resourceFormat(data)];

            if (!data.parent) return newVersions;

            return format(data.parent, newVersions);
        };

        return format(
            (
                await core({
                    url: `/v2/resource/${resourceId}/versions`,
                    method: 'GET',
                })
            ).data
        );
    };

    return {
        getForResource,
    };
};
