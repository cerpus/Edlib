import React from 'react';

const context = React.createContext({
    onInsert: null,
});

export const ResourceCapabilitiesProvider = context.Provider;

export const useResourceCapabilities = () => React.useContext(context);
