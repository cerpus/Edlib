import env from '@cerpus/edlib-node-utils/services/env.js';

export default {
    version: {
        url: env('VERSIONAPI_URL', 'http://versioningapi:8080'),
    },
    elasticsearch: {
        resourceIndexPrefix: 'edlib-resources',
        url: env('ELASTICSEARCH_URL', 'http://elasticsearch-latest:9200'),
    },
    externalResourceAPIS: {
        contentauthor: {
            url: 'https://contentauthor.local/v1/content',
            ltiUrl: 'https://contentauthor.local/lti-content',
            getAllGroups: ['h5p', 'questionset', 'article', 'game'],
            ltiConsumerSecret: 'secret2',
            ltiConsumerKey: 'h5p',
        },
    },
};
