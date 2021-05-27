import context from '../context/index.js';

export default (req, res, next) => {
    req.context = context(req, res);
    next();
};
