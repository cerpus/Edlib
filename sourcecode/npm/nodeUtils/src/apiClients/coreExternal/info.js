import helpers from '@cerpus-private/edlib-node-utils/helpers/index.js';
import { NotFoundException } from '@cerpus-private/edlib-node-utils/exceptions/index.js';

export default (core) => {
    const get = async (type, id, level = 0) => {
        const response = await core({
            url: `/v2/info/${type}/${id}`,
            method: 'GET',
        });

        if (response.status !== 202) {
            return response.data;
        }

        if (level > 3) {
            throw new NotFoundException('coreId');
        }

        await helpers.delayAsync(500);

        return get(type, id, level + 1);
    };

    return {
        get,
    };
};
