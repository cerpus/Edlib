import React from 'react';
import request from '../helpers/request';
import { useEdlibComponentsContext } from '../contexts/EdlibComponents';
import debug from 'debug';

const log = debug('edlib-components:useRequestWithToken');

export default () => {
    const { jwt } = useEdlibComponentsContext();

    return async (url, method, options = {}) => {
        let token = await jwt.getToken();
        log('Using following token for request ', token);

        return await request(url, method, {
            ...options,
            headers: {
                ...options.headers,
                Authorization: `Bearer ${token}`,
            },
        });
    };
};
