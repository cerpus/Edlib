import { middlewares } from '@cerpus/edlib-node-utils';

const convert = async (req) => {
    if (!req.body.ext_jwt_token) {
        return;
    }

    const { token } = await req.context.services.edlibAuth.convertToken(
        req.body.ext_jwt_token
    );

    req.cookies.jwt = token;
};

export default (req, res, next) => {
    convert(req)
        .then(() => middlewares.addUserToRequest(req, res, next))
        .catch(next);
};
