import appConfig from '../envConfig/app.js';

export const addCookieToRes = (res, name, value, expireHours = 72, options) =>
    res.cookie(name, value, {
        maxAge: 1000 * 60 * 60 * expireHours,
        httpOnly: true,
        sameSite: 'none',
        secure: appConfig.isProduction,
        domain: appConfig.cookieDomain,
        ...options,
    });

export const clearCookie = (res, name) =>
    res.clearCookie(name, {
        httpOnly: true,
        sameSite: 'none',
        secure: appConfig.isProduction,
        domain: appConfig.cookieDomain,
    });
