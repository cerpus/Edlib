import services from './services/index.js';

export const buildRawContext = (req, res) => ({
    services: services(req),
});

const getContext = (req, res) => ({
    user: req.user,
    res,
    req,
    ...buildRawContext(req, res),
});

export default getContext;
