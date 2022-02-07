import user from '../repositories/user.js';
import jwksKey from '../repositories/jwksKey.js';
import ltiUser from '../repositories/ltiUser.js';
import tenantAuthMethod from '../repositories/tenantAuthMethod.js';
import tenantAuthMethodAuthZero from '../repositories/tenantAuthMethodAuthZero.js';
import tenantAuthMethodCerpusAuth from '../repositories/tenantAuthMethodCerpusAuth.js';

export const buildRawContext = (req = {}, res = {}, { pubSubConnection }) => ({
    db: {
        user: user(),
        jwksKey: jwksKey(),
        ltiUser: ltiUser(),
        tenantAuthMethod: tenantAuthMethod(),
        tenantAuthMethodAuthZero: tenantAuthMethodAuthZero(),
        tenantAuthMethodCerpusAuth: tenantAuthMethodCerpusAuth(),
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
