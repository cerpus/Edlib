import axios from 'axios';
import crypto from 'crypto';
import embedlyConfig from '../../config/embedly.js';
import { redisHelpers } from '@cerpus/edlib-node-utils';

const getForUrl = redisHelpers.cacheWrapper(
    (args) =>
        `embedly-${crypto
            .createHash('md5')
            .update(args.join(','))
            .digest('hex')}`,
    async (url) => {
        const response = await axios.get('https://api.embedly.com/1/oembed', {
            params: { url, key: embedlyConfig.key },
        });

        return response.data;
    }
);

export default () => ({
    getForUrl,
});
