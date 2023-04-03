import React from 'react';
import { CookieStorage } from 'cookie-storage';

const cookieStorage = new CookieStorage();

const ConfigurationContext = React.createContext({});
const prefix = 'edlib-configuration-';

export const ConfigurationProvider = ({
    children,
    apiUrl,
    wwwUrl,
    isSSR = false,
    cookies,
}) => {
    return (
        <ConfigurationContext.Provider
            value={{
                edlibApi: (path) => `${apiUrl}${path}`,
                www: (path) => `${wwwUrl}${path}`,
                isSSR,
                getConfigurationValue: (name, defaultValue = null) => {
                    const actualName = `${prefix}${name}`;
                    if (isSSR) {
                        return cookies[actualName] || defaultValue;
                    }

                    return cookieStorage.getItem(actualName) || defaultValue;
                },
                setConfigurationValue: (name, value) => {
                    const actualName = `${prefix}${name}`;

                    if (isSSR) {
                        return;
                    }

                    cookieStorage.setItem(actualName, value);
                },
            }}
        >
            {children}
        </ConfigurationContext.Provider>
    );
};

ConfigurationProvider.defaultProps = {
    enableVersionInterface: false,
    enableTranslationButton: false,
    enableCollections: false,
    inMaintenanceMode: false,
};

export const useConfigurationContext = () =>
    React.useContext(ConfigurationContext);
