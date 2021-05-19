import context from '../context/index.js';

export default ({pubSubConnection}) =>(req, res, next) => {
    req.context = context(req, res, {pubSubConnection});
    next();
};
