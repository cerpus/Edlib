import doku from '../repositories/doku.js';
import services from './services/index.js';

export const buildRawContext = (req) => ({
    db: {
        doku: doku(),
    },
    services: services(req),
});

const getContext = (req, res) => {
    return {
        user: req.user,
        res,
        req,
        ...buildRawContext(req),
    };
};

export default getContext;
