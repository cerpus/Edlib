import validateJoi from '../services/validateJoi.js';

export default (validationObject) => (req, res, next) => {
    req.body = validateJoi(req.body, validationObject);
    next();
};
