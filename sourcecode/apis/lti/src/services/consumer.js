export default {
    createOrUpdate: async (context, key, secret) => {
        const dbConsumer = await context.db.consumer.getByKey(key);

        if (dbConsumer) {
            return await context.db.consumer.update(dbConsumer.id, {
                key,
                secret,
            });
        }

        return await context.db.consumer.create({
            key,
            secret,
        });
    },
};
