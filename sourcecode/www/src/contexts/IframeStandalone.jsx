import React from 'react';

const IframeStandaloneContext = React.createContext({
    getPath: (path) => path,
});

export const IframeStandaloneProvider = ({ children, basePath }) => {
    return (
        <IframeStandaloneContext.Provider
            value={{
                getPath: (path) => basePath + path,
            }}
        >
            {children}
        </IframeStandaloneContext.Provider>
    );
};

export const useIframeStandaloneContext = () =>
    React.useContext(IframeStandaloneContext);
