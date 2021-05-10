export default {
    createUsageView: async (req) => {
        const usage = await req.context.db.usage.getById(req.params.usageId);

        return await req.context.db.usageView.create({
            usageId: usage.id,
            tenantId: req.body.tenantId,
        });
    },
};
