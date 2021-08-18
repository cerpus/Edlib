import { ApiException } from '@cerpus/edlib-node-utils';

export default (callback, returnType = 'json') => (req, res, next) =>
    callback(req, res, next)
        .then((json) => json && res.json(json))
        .catch((err) => {
            if (returnType === 'html') {
                if (err instanceof ApiException) {
                    res.sendStatus(err.getStatus());
                    return;
                }
            }
            next(err);
        });
