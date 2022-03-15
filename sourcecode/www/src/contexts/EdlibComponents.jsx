import React from 'react';
import urls from '../config/urls';
import useToken from '../hooks/useToken';
import _ from 'lodash';
import Joi from 'joi';
import resourceEditors from '../constants/resourceEditors';
import resourceFilters from '../constants/resourceFilters';
import nbTranslations from '../i18n/nb/translation.json';
import resourceColumns from '../constants/resourceColumns';

const EdlibComponentContext = React.createContext({
    jwt: null,
    config: {
        coreUrl: urls.defaultCoreUrl,
    },
});

const configurationValidationSchema = Joi.object({
    landingContentExplorerPage: Joi.string()
        .valid('sharedContent', 'myContent')
        .allow(null),
    enabledResourceTypes: Joi.array()
        .items(Joi.string().valid(...Object.values(resourceEditors)))
        .allow(null),
    disabledFilters: Joi.array()
        .items(Joi.string().valid(...Object.values(resourceFilters)))
        .allow(null),
    approvedH5ps: Joi.array()
        .items(
            Joi.string()
                .valid(
                    ...Object.keys(nbTranslations.h5pTypes.H5P).map(
                        (type) => `H5P.${type}`
                    )
                )
                .insensitive()
        )
        .allow(null),
    canReturnResources: Joi.boolean().default(true),
    hideResourceColumns: Joi.array()
        .items(
            Joi.string()
                .valid(...Object.values(resourceColumns))
                .insensitive()
        )
        .default([]),
    returnLtiLinks: Joi.boolean().default(true),
});

export const EdlibComponentsProvider = ({
    children,
    externalJwt = null,
    language = 'nb',
    configuration = {},
}) => {
    const tokenControllerData = useToken(externalJwt);

    const validatedConfiguration = React.useMemo(() => {
        const { value, error } =
            configurationValidationSchema.validate(configuration);

        if (error) {
            console.error(
                'Configuration validation failed. Using default configuration: ',
                error
            );
            return null;
        }

        return value;
    }, [configuration]);

    return (
        <EdlibComponentContext.Provider
            value={{
                tokenControllerData,
                language,
                getUserConfig: (path) => _.get(validatedConfiguration, path),
            }}
        >
            {children}
        </EdlibComponentContext.Provider>
    );
};

export const useEdlibComponentsContext = () =>
    React.useContext(EdlibComponentContext);
