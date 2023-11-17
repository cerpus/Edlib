import moment from 'moment';
import { v4 as uuidv4 } from 'uuid';
import CryptoJS from 'crypto-js';
import oauthSignature from 'oauth-signature';
import {
    ValidationException,
    validationExceptionError,
} from '@cerpus/edlib-node-utils';

const allowedParameters = [
    'context_type',
    'launch_presentation_return_url',
    'selection_directive',
    'ext_content_return_types',
    'ext_content_return_url',
    'ext_content_intended_use',
    'launch_presentation_document_target',
    'resource_link_id',
    'resource_link_title',
    'ext_jwt_token',
    'ext_h5p_only',
    'context_id',
    'ext_course_id',
    'ext_module_id',
    'ext_activity_id',
    'user_id',
    'lis_person_name_given',
    'lis_person_name_family',
    'lis_person_contact_email_primary',
    'ext_user_id',
    'lis_person_sourcedid',
    'ext_user_username',
    'launch_presentation_css_url',
    'launch_presentation_locale',
    'tool_consumer_info_product_family_code',
    'tool_consumer_instance_url',
    'ext_create_content_default_license',
    'ext_behavior_settings',
    'ext_preview',
    'ext_embed_id',
];

const buildLtiRequest = (
    url,
    consumerKey,
    consumerSecret,
    authorization,
    extras = {}
) => {
    const resourceLinkId = `random_link_${uuidv4()}`;

    const filteredExtras = Object.entries(extras).reduce(
        (result, [key, value]) => {
            if (allowedParameters.indexOf(key) !== -1) {
                result[key] = value;
            }

            return result;
        },
        {}
    );

    const fields = {
        lti_version: 'LTI-1p0',
        lti_message_type: 'basic-lti-launch-request',
        oauth_consumer_key: consumerKey,
        resource_link_id: resourceLinkId,
        oauth_version: '1.0',
        oauth_nonce: CryptoJS.MD5(
            `${uuidv4()}-${Math.random()}-${process.hrtime()}`
        ).toString(),
        oauth_timestamp: moment().unix(),
        oauth_signature_method: 'HMAC-SHA1',
        ...filteredExtras,
    };

    if (authorization && authorization.jwt) {
        fields.ext_jwt_token = authorization.jwt;
    }

    if (authorization && authorization.userId) {
        fields.ext_user_id = authorization.userId;
    }

    if (authorization?.user?.firstName) {
        fields.lis_person_name_given = authorization.user.firstName;
    }
    
    if (authorization?.user?.lastName) {
        fields.lis_person_name_family = authorization.user.lastName;
    }

    if (authorization?.user?.email) {
        fields.lis_person_contact_email_primary = authorization.user.email;
    }

    if (fields.ext_preview === '1' || fields.ext_preview === true) {
        fields.ext_preview = 'true';
    }

    fields.oauth_signature = oauthSignature.generate(
        'POST',
        url,
        fields,
        consumerSecret,
        '',
        { encodeSignature: false }
    );

    return {
        url: url,
        method: 'POST',
        params: fields,
    };
};

const viewResourceRequest = async (
    context,
    resourceId,
    resourceVersionId,
    authorization,
    extras
) => {
    const {
        url: ltiResourceUrl,
        consumerSecret,
        consumerKey,
        resourceVersion,
    } = await context.services.resource.getLtiResourceInfo(
        extras.ext_preview === 'true' || extras.ext_preview === '1'
            ? authorization
            : {},
        resourceId,
        resourceVersionId
    );

    const { version } = await context.services.resource.getResourceWithVersion(
        resourceId,
        resourceVersionId
    );

    return {
        resourceVersion,
        launchRequest: buildLtiRequest(
            ltiResourceUrl,
            consumerKey,
            consumerSecret,
            authorization,
            {
                ...extras,
                ext_embed_id: resourceId,
                resource_link_title: version.title,
            },
        ),
    };
};

const editResourceRequest = async (
    context,
    resourceId,
    resourceVersionId,
    authorization,
    reqProtoHost,
    language
) => {
    const {
        url: ltiResourceUrl,
        consumerSecret,
        consumerKey,
        resourceVersion,
    } = await context.services.resource.getLtiResourceInfo(
        authorization,
        resourceId,
        resourceVersionId
    );

    let launch_presentation_locale;

    if (language) {
        launch_presentation_locale = language;
    }

    return buildLtiRequest(
        ltiResourceUrl + '/edit',
        consumerKey,
        consumerSecret,
        authorization,
        {
            launch_presentation_locale,
            launch_presentation_return_url:
                reqProtoHost +
                `/lti/v2/editors/${resourceVersion.externalSystemName.toLowerCase()}/return`,
        }
    );
};

const createResourceRequest = async (
    context,
    externalSystemName,
    externalSystemGroup,
    authorizationJwt,
    extras
) => {
    const {
        url: ltiResourceUrl,
        consumerSecret,
        consumerKey,
    } = await context.services.resource.getLtiCreateInfo(
        externalSystemName,
        externalSystemGroup
    );

    return buildLtiRequest(
        ltiResourceUrl,
        consumerKey,
        consumerSecret,
        authorizationJwt,
        extras
    );
};

const validateRequest = async (context, url, params) => {
    const consumer = await context.services.lti.getConsumerByKey(
        params.oauth_consumer_key
    );

    const requestSignature = params.oauth_signature;

    const paramsWithoutSignature = { ...params };

    delete paramsWithoutSignature.oauth_signature;

    const generatedSignature = oauthSignature.generate(
        'POST',
        url,
        paramsWithoutSignature,
        consumer.secret,
        '',
        { encodeSignature: false }
    );

    if (generatedSignature !== requestSignature) {
        throw new ValidationException(
            validationExceptionError('signature', 'body', 'Invalid signature')
        );
    }

    return params;
};

export default {
    viewResourceRequest,
    validateRequest,
    createResourceRequest,
    editResourceRequest,
};
