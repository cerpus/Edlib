export default {
    dokuApiSystemStatus: (req, res, next) =>
        req.context.services.doku.systemStatus(),
};
