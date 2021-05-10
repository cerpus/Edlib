import resource from '../repositories/resource.js';
import resourceVersion from '../repositories/resourceVersion.js';
import services from './services/index.js';
import resourceGroup from '../repositories/resourceGroup.js';

export const buildRawContext = (req = {}, res = {}) => ({
    services: services(req, res),
    db: {
        resource: resource(),
        resourceVersion: resourceVersion(),
        resourceGroup: resourceGroup(),
    },
});

const getContext = (req, res) => ({
    user: req.user,
    res,
    req,
    ...buildRawContext(req, res),
});

export default getContext;
