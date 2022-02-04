import apiConfig from '../config/api.js';

export class RequestError extends Error {
    constructor(response) {
        super(`Request failed with status ${response.status}`);
        this.response = response;
    }
}

const request = async (url, method, options = {}) => {
    let actualUrl = `${apiConfig.url}${url}`;

    if (options.query) {
        actualUrl += `?${queryParams(options.query)}`;
    }

    const response = await fetch(actualUrl, {
        credentials: 'include',
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

    try {
        return await response.json();
    } catch (e) {
        return null;
    }
};

export default request;

const getHeaders = (options) => {
    const headers = { ...options.headers };

    if (options.body) {
        headers['Content-Type'] = 'application/json';
    }

    return headers;
};

const queryParams = (params) =>
    Object.keys(params)
        .map((k) => encodeURIComponent(k) + '=' + encodeURIComponent(params[k]))
        .join('&');
