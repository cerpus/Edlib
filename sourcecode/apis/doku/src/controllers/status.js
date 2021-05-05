export default {
    systemStatus: async (req, res, next) => {
        const systems = [
            await req.context.services.status.version(),
            await req.context.services.status.license(),
            await req.context.services.status.db(),
        ];

        return req.context.services.status.parser('DokuAPI', systems);
    },
};
