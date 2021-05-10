import { validateJoi } from '@cerpus/edlib-node-utils/services/index.js';
import Joi from 'joi';
import fileParserService from '../services/fileParser.js';

const externalTokenVerifier = fileParserService.getConfigurationValuesFromSetupFile(
    'externalTokenVerifier.yaml'
);

validateJoi(
    externalTokenVerifier,
    Joi.object().keys({
        wellKnownEndpoint: Joi.string().uri().required(),
        propertyPaths: Joi.object().keys({
            id: Joi.string().required(),
            email: Joi.string().required(),
            firstName: Joi.string().required(),
            lastName: Joi.string().required(),
        }),
    })
);

export default {
    wellKnownEndpoint: externalTokenVerifier.wellKnownEndpoint,
    propertyPaths: {
        id: externalTokenVerifier.propertyPaths.id,
        firstName: externalTokenVerifier.propertyPaths.firstName,
        lastName: externalTokenVerifier.propertyPaths.lastName,
        email: externalTokenVerifier.propertyPaths.email,
    },
};
