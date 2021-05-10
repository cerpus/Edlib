import user from '../repositories/user.js';
import jwksKey from '../repositories/jwksKey.js';

export const buildRawContext = (req = {}, res = {}) => ({
    db: {
        user: user(),
        jwksKey: jwksKey(),
    },
});

const getContext = (req, res) => ({
    user: req.user,
    res,
    req,
    ...buildRawContext(req, res),
});

export default getContext;
