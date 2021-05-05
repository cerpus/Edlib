import services from './services';

export const buildRawContext = (req, res) => ({
    services: services(req, res),
});

const getContext = (req, res) => ({
    user: req.user,
    res,
    req,
    ...buildRawContext(req, res),
});

export default getContext;
