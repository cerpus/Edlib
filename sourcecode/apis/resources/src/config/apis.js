import env from '@cerpus/edlib-node-utils/services/env.js';

const contentAuthorUrl = env(
    'EDLIBCOMMON_CONTENTAUTHOR_URL',
    'https://contentauthor.local'
);

export default {
    version: {
        url: env('VERSIONAPI_URL', 'http://versioningapi:8080'),
    },
    elasticsearch: {
        resourceIndexPrefix: 'edlib-resources',
        url: env(
            'EDLIBCOMMON_ELASTICSEARCH_URL',
            'http://elasticsearch-latest:9200'
        ),
    },
    externalResourceAPIS: {
        contentauthor: {
            url: `${contentAuthorUrl}/v1/content`,
            ltiUrl: `${contentAuthorUrl}/lti-content`,
            getAllGroups: ['h5p', 'questionset', 'article', 'game'],
            ltiConsumerKey: env(
                'EDLIBCOMMON_CONTENTAUTHOR_CONSUMER_KEY',
                'h5p'
            ),
            ltiConsumerSecret: env(
                'EDLIBCOMMON_CONTENTAUTHOR_CONSUMER_SECRET',
                'secret2'
            ),
        },
    },
    coreInternal: {
        url: env('EDLIBCOMMON_CORE_INTERNAL_URL', 'http://core'),
    },
};
