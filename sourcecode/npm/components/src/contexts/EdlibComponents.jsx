import React from 'react';
import i18n from '../i18n';
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
});

export const EdlibComponentsProvider = ({
    children,
    getJwt = null,
    language = 'nb',
    dokuUrl = null,
    edlibUrl = null,
    configuration = {},
}) => {
    const actualEdlibApiUrl =
        !edlibUrl || edlibUrl.length === 0 ? urls.defaultEdlibUrl : edlibUrl;

    const edlibFrontendUrl = actualEdlibApiUrl.replace('api', 'www');

    const { token, error, loading, getToken } = useToken(
        getJwt,
        actualEdlibApiUrl
    );

    React.useEffect(() => {
        i18n.changeLanguage(language);
    }, [language]);

    const validatedConfiguration = React.useMemo(() => {
        const { value, error } = configurationValidationSchema.validate(
            configuration
        );

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
                jwt: {
                    value: token,
                    loading: loading,
                    error: error,
                    getToken,
                },
                config: {
                    urls: {
                        edlibUrl: actualEdlibApiUrl,
                        edlibFrontendUrl,
                        dokuUrl:
                            !dokuUrl || dokuUrl.length === 0
                                ? urls.defaultDokuUrl
                                : dokuUrl,
                        ndlaUrl: urls.ndla,
                        ndlaApiUrl: urls.ndlaApi,
                    },
                },
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
