import { EndpointNotFoundException } from '../exceptions/index.js';

export default (req, res, next) => {
    next(new EndpointNotFoundException(req.url, req.method));
};
