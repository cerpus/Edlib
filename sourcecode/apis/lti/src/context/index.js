import usage from '../repositories/usage.js';
import services from './services/index.js';
import consumer from '../repositories/consumer.js';
import usageView from '../repositories/usageView.js';

export const buildRawContext = (req, res) => ({
    services: services(req, res),
    db: {
        usage: usage(),
        consumer: consumer(),
        usageView: usageView(),
    },
});

const getContext = (req, res) => ({
    user: req.user,
    res,
    req,
    ...buildRawContext(req, res),
});

export default getContext;
