import React from 'react';
import request from '../helpers/request';
import debug from 'debug';
import { useTokenContext } from '../contexts/token.js';

const log = debug('edlib-admin:useRequestWithToken');

const useRequestWithToken = () => {
    const { jwt } = useTokenContext();

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

export default useRequestWithToken;
