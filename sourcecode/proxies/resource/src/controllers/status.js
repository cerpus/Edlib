export default {
    systemStatus: async (req, res, next) => {
        const systems = [
            await req.context.services.status.auth(),
            await req.context.services.status.license(),
            await req.context.services.status.coreExternal(),
            await req.context.services.status.id(),
        ];

        return {
            name: 'EdLibAPI - Resources',
            status: 'All good',
            color: 'success',
            systems,
        };
    },
};
