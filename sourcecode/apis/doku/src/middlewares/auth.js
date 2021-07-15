import { UnauthorizedException } from '@cerpus/edlib-node-utils';

export const authTypes = {
    CERPUS_ADMIN: 'cerpus_admin',
    CERPUS_USER: 'cerpus_user',
    SYSTEM_SERVICE: 'system_service',
};

const internalToken = async (req) => {
    try {
        if (!req.headers['x-internal-user']) {
            return false;
        }

        req.user = JSON.parse(
            new Buffer(req.headers['x-internal-user'], 'base64').toString()
        );

        return true;
    } catch (e) {
        return false;
    }
};

const cerpusUser = async (req) => {
    return await internalToken(req);
};

const cerpusAdmin = async (req) => {
    const isLoggedIn = await cerpusUser(req);

    if (!isLoggedIn) {
        return false;
    }

    return req.user.admin;
};

const systemService = async (req) => {
    // @todo implement when Jan-Espen has fixed EDL-674
    return false;
};

export default (config) => async (req, res, next) => {
    let allowedAdapters = Array.isArray(config) ? config : [config];
    let isAllowed = false;

    try {
        for (let allowedAdapter of allowedAdapters) {
            if (isAllowed) {
                continue;
            }

            switch (allowedAdapter) {
                case authTypes.CERPUS_USER:
                    isAllowed = await cerpusUser(req);
                    break;
                case authTypes.CERPUS_ADMIN:
                    isAllowed = await cerpusAdmin(req);
                    break;
                case authTypes.SYSTEM_SERVICE:
                    isAllowed = await systemService(req);
                    break;
                default:
                    break;
            }
        }

        if (!isAllowed) {
            next(new UnauthorizedException());
        }
    } catch (e) {
        next(e);
    }

    next();
};
