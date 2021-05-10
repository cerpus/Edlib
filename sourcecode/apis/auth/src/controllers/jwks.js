import jwksProviderService from '../services/jwksProvider.js';

export default {
    getJwks: async (req, res, next) => {
        return jwksProviderService.wellKnownJwks(req.context);
    },
};
