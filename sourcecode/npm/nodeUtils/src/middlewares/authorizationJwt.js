const getToken = (req) => {
    const bearerString = 'Bearer';
    const authorization = req.headers.authorization;
    const jwtCookie = req.cookies.jwt;
    const jwtQuery = req.query.jwt;

    if (jwtQuery) {
        return jwtQuery;
    }

    if (authorization && authorization.startsWith(bearerString)) {
        return authorization.substring(bearerString.length).trim();
    }

    if (jwtCookie) {
        return jwtCookie;
    }

    return null;
};

export default (req, res, next) => {
    req.authorizationJwt = getToken(req);

    next();
};
