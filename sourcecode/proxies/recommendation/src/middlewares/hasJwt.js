import { UnauthorizedException } from '@cerpus/edlib-node-utils';

export default (req, res, next) => {
    const bearerString = 'Bearer';
    const authorization = req.headers.authorization;

    if (!authorization || !authorization.startsWith(bearerString)) {
        throw new UnauthorizedException();
    }

    req.authorizationJwt = authorization.substring(bearerString.length).trim();

    next();
};
