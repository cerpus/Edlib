import user from '../repositories/user.js';
import jwksKey from '../repositories/jwksKey.js';
import ltiUser from '../repositories/ltiUser.js';

export const buildRawContext = (req = {}, res = {}, { pubSubConnection }) => ({
    db: {
        user: user(),
        jwksKey: jwksKey(),
        ltiUser: ltiUser(),
    },
    pubSubConnection,
});

const getContext = (req, res, { pubSubConnection }) => ({
    user: req.user,
    res,
    req,
    ...buildRawContext(req, res, { pubSubConnection }),
});

export default getContext;
