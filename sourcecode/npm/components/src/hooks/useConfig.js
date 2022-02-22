import React from 'react';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';

export default () => {
    const { config } = useEdlibComponentsContext();

    return {
        edlib: (path) => `${config.urls.edlibUrl}${path}`,
        edlibFrontend: (path) => `${config.urls.edlibFrontendUrl}${path}`,
    };
};
