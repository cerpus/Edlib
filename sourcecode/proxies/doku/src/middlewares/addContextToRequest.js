import context from '../context';

export default (req, res, next) => {
    req.context = context(req, res);
    next();
};
