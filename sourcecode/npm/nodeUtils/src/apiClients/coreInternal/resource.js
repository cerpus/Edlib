import { NotFoundException } from '../../exceptions/index.js';
import helpers from '../../helpers/index.js';

export default (core) => {
    const fromExternalIdInfo = async (type, id, level = 0) => {
        const response = await core({
            url: `/v2/info/${type}/${id}`,
            method: 'GET',
        });

        if (response.status !== 202) {
            return response.data;
        }

        if (level > 30) {
            throw new NotFoundException('coreId');
        }

        await helpers.delayAsync(200);

        return fromExternalIdInfo(type, id, level + 1);
    };

    const structure = async (resourceId) => {
        const response = await core({
            url: `/v2/resource/${resourceId}/structure`,
            method: 'GET',
        });

        return response.data;
    };

    const info = async (resourceId, level = 0) => {
        const response = await core({
            url: `/v2/resource/${resourceId}/info`,
            method: 'GET',
        });

        if (response.status !== 202) {
            return response.data;
        }

        if (level > 30) {
            throw new NotFoundException('resource');
        }

        await helpers.delayAsync(200);

        return info(resourceId, level + 1);
    };

    const convertLaunchUrlToEdlibId = async (launchUrl) => {
        const response = await core({
            url: `/v2/resource-id-from-launch-url`,
            method: 'GET',
            params: {
                launchUrl,
            },
        });

        return response.data.edlibId;
    };

    return {
        fromExternalIdInfo,
        structure,
        info,
        convertLaunchUrlToEdlibId,
    };
};
