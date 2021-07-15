import services from './services/index.js';

export const buildRawContext = (req, res, { pubSubConnection }) => ({
    services: services(req, res),
    pubSubConnection,
});

const getContext = (req, res, { pubSubConnection }) => ({
    user: req.user,
    res,
    req,
    reqProtoHost: req.protocol + '://' + req.get('host'),
    ...buildRawContext(req, res, { pubSubConnection }),
});

export default getContext;
