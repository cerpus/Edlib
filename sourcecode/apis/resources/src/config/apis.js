import { env } from '@cerpus/edlib-node-utils';

const contentAuthorUrl = env(
    'EDLIBCOMMON_CONTENTAUTHOR_URL',
    `https://ca.${env('EDLIB_ROOT_DOMAIN', 'edlib.test')}`
);

const edlibUrl = env('EDLIBCOMMON_URL', `https://api.${env('EDLIB_ROOT_DOMAIN', 'edlib.test')}`);

export default {
    elasticsearch: {
        resourceIndexPrefix: 'edlib-resources',
        url: env('EDLIBCOMMON_ELASTICSEARCH_URL', 'http://elasticsearch:9200'),
    },
    externalResourceAPIS: {
        contentauthor: {
            urls: {
                content: `${contentAuthorUrl}/v1/content`,
                contentType: `${contentAuthorUrl}/internal/v1/content-types`,
                lti: `${contentAuthorUrl}/lti-content`,
                view: `${contentAuthorUrl}/view`,
                contentVersion: `${contentAuthorUrl}/internal/v1/content-version/`,
            },
            httpAuthKey: env(
                'EDLIBCOMMON_CONTENTAUTHOR_INTERNAL_KEY',
                'secret'
            ),
            url: `${contentAuthorUrl}/v1/content`,
            ltiUrl: `${contentAuthorUrl}/lti-content`,
            getAllGroups: ['h5p', 'questionset', 'article', 'game'],
            disableVersioningGroups: ['questionset'],
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
    edlibAuth: {
        url: env('EDLIB_AUTH_URL', 'http://authapi'),
    },
    lti: {
        url: env('LTI_API_URL', 'http://ltiapi'),
    },
};
