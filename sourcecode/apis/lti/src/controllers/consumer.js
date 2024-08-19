import { NotFoundException } from '@cerpus/edlib-node-utils';

export default {
    getConsumerByKey: async (req) => {
        const dbConsumer = await req.context.db.consumer.getByKey(
            req.params.key
        );

        if (!dbConsumer) {
            throw new NotFoundException('consumer');
        }

        return dbConsumer;
    },
};
