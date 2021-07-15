export default {
    systemStatus: async (req, res, next) => {
        const systems = [
            await req.context.services.status.auth(),
            await req.context.services.status.coreExternal(),
        ];

        return {
            name: 'EdLibAPI - Lti',
            status: 'All good',
            color: 'success',
            systems,
        };
    },
};
