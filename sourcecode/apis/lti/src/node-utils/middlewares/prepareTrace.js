import * as errorReporting from '../services/errorReporting.js';

export default (req, res, next) => {
    errorReporting.setupTrace(req, res);

    next();
};
