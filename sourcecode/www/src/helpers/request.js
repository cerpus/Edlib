import fetch from 'isomorphic-unfetch';
import * as queryString from 'query-string';

export class RequestError extends Error {
    constructor(response) {
        super(`Request failed with status ${response.status}`);
        this.response = response;
    }
}

const request = async (url, method, options = {}) => {
    let actualUrl = url;

    if (options.query) {
        actualUrl += `?${queryParams(options.query)}`;
    }

    const response = await fetch(actualUrl, {
        headers: getHeaders(options),
        method,
        signal: options.signal,
        body: options.body ? JSON.stringify(options.body) : undefined,
        credentials: 'include',
    });

    if (response.status >= 400) {
        try {
            response.data = await response.json();
        } catch (e) {}
        throw new RequestError(response);
    }

    if (options.addCookiesFromSetCookie) {
        options.addCookiesFromSetCookie(response.headers.get('set-cookie'));
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

    if (options.cookies) {
        headers['cookie'] = Object.entries(options.cookies)
            .map(([key, value]) => `${key}=${value}`)
            .join(';');
    }

    return headers;
};

const queryParams = (params) =>
    queryString.stringify(params, { arrayFormat: 'bracket' });

export default request;
