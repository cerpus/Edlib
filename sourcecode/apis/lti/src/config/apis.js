import { env } from '@cerpus/edlib-node-utils';

export default {
    resource: {
        url: env('RESOURCE_API_URL', 'http://resourceapi'),
    },
};
