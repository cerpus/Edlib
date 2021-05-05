import { NotFoundException } from '../../exceptions/index.js';
import helpers from '../../helpers/index.js';

export default (core) => {
    const info = async (resourceId, level) => {
        const response = await core({
            url: `/v2/resource/${resourceId}/info`,
            method: 'GET',
        });

        if (response.status !== 202) {
            return response.data;
        }

        if (level > 3) {
            throw new NotFoundException('resource');
        }

        await helpers.delayAsync(500);

        return info(resourceId, level + 1);
    };

    const remove = async (resourceId) => {
        return (
            await core({
                url: `/v2/resource/${resourceId}`,
                method: 'DELETE',
            })
        ).data;
    };

    return {
        info,
        remove,
    };
};
