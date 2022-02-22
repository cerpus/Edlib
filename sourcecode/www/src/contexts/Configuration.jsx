import React from 'react';

const ConfigurationContext = React.createContext({});

export const ConfigurationProvider = ({ children, ...props }) => {
    return (
        <ConfigurationContext.Provider value={props}>
            {children}
        </ConfigurationContext.Provider>
    );
};

ConfigurationProvider.defaultProps = {
    enableVersionInterface: false,
    enableTranslationButton: false,
    enableDoku: false,
    enableCollections: false,
    inMaintenanceMode: false,
};

export const useConfigurationContext = () =>
    React.useContext(ConfigurationContext);
