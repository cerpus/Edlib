import React from 'react';
import request from '../helpers/request';

export default () => {
    return async (url, method, options = {}) => {
        return await request(url, method, options);
    };
};
