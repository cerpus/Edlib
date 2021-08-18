export default {
    systemStatus: async (req, res, next) => {
        const systems = [await req.context.services.status.auth()];

        return {
            name: 'EdLibAPI - Auth',
            status: 'All good',
            color: 'success',
            systems,
        };
    },
};
