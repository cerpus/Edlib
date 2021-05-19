import usage from '../repositories/usage.js';
import services from './services/index.js';
import consumer from '../repositories/consumer.js';
import usageView from '../repositories/usageView.js';
import sync from '../repositories/sync.js';
import consumerUser from '../repositories/consumerUser.js';

export const buildRawContext = (req, res, { pubSubConnection }) => ({
    services: services(req, res),
    db: {
        usage: usage(),
        consumer: consumer(),
        consumerUser: consumerUser(),
        usageView: usageView(),
        sync: sync(),
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
