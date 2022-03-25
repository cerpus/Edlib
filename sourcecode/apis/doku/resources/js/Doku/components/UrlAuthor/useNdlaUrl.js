import React from 'react';
import useConfig from '../../hooks/useConfig';

export default (url) => {
    const { ndlaUrl } = useConfig();
    const parsedUrl = new URL(url);

    if (parsedUrl.origin === ndlaUrl) {
        const urlResourcePart = parsedUrl.pathname
            .split('/')
            .find((urlPart) => urlPart.startsWith('resource:1:'));

        if (!urlResourcePart) {
            return null;
        }

        return urlResourcePart.split(':')[2];
    }

    return null;
};
