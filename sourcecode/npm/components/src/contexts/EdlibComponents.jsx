import React from 'react';
import urls from '../config/urls';
import _ from 'lodash';

const EdlibComponentContext = React.createContext({
    jwt: null,
    config: {},
});

export const EdlibComponentsProvider = ({
    children,
    getJwt = null,
    language = 'en',
    edlibUrl = null,
    configuration = {},
}) => {
    const actualEdlibApiUrl =
        !edlibUrl || edlibUrl.length === 0 ? urls.defaultEdlibUrl : edlibUrl;

    const edlibFrontendUrl = actualEdlibApiUrl.replace('api', 'www');

    return (
        <EdlibComponentContext.Provider
            value={{
                getJwt,
                config: {
                    urls: {
                        edlibUrl: actualEdlibApiUrl,
                        edlibFrontendUrl,
                    },
                },
                language,
                configuration,
                getUserConfig: (path) => _.get(configuration, path),
            }}
        >
            {children}
        </EdlibComponentContext.Provider>
    );
};

export const useEdlibComponentsContext = () =>
    React.useContext(EdlibComponentContext);
