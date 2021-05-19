import resource from '../repositories/resource.js';
import resourceVersion from '../repositories/resourceVersion.js';
import services from './services/index.js';
import resourceGroup from '../repositories/resourceGroup.js';
import sync from '../repositories/sync.js';

export const buildRawContext = (req = {}, res = {}, { pubSubConnection }) => ({
    services: services(req, res),
    db: {
        resource: resource(),
        resourceVersion: resourceVersion(),
        resourceGroup: resourceGroup(),
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
