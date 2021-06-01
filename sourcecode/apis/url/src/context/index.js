import url from '../repositories/url.js';
import services from './services/index.js';
import sync from '../repositories/sync.js';

export const buildRawContext = (req = {}, res = {}, { pubSubConnection }) => ({
    db: {
        url: url(),
        sync: sync(),
    },
    services: services(req, res),
    pubSubConnection,
});

const getContext = (req, res, { pubSubConnection }) => ({
    user: req.user,
    res,
    req,
    ...buildRawContext(req, res, { pubSubConnection }),
});

export default getContext;
