export default (callback) => (req, res, next) =>
    callback(req, res, next)
        .then((json) => json && res.json(json))
        .catch(next);
