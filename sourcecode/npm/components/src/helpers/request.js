import React from 'react';
import * as queryString from 'query-string';

export class RequestError extends Error {
    constructor(response) {
        super(`Request failed with status ${response.status}`);
        this.response = response;
    }
}

export default async (url, method, options = {}) => {
    let actualUrl = url;

    if (options.query) {
        actualUrl += `?${queryParams(options.query)}`;
    }

    const response = await fetch(actualUrl, {
        headers: getHeaders(options),
        method,
        signal: options.signal,
        body: options.body ? JSON.stringify(options.body) : undefined,
    });

    if (response.status >= 400) {
        response.data = await response.json();
        throw new RequestError(response);
    }

    if (options.json === false) {
        return response;
    }

    return await response.json();
};

const getHeaders = (options) => {
    const headers = { ...options.headers };

    if (options.body) {
        headers['Content-Type'] = 'application/json';
    }

    return headers;
};

const queryParams = (params) =>
    queryString.stringify(params, { arrayFormat: 'bracket' });
