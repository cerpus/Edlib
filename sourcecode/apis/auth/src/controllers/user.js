import { NotFoundException } from '@cerpus/edlib-node-utils';

export default {
    getUsersByEmail: async (req) => {
        return req.context.db.user.getByEmails(req.body.emails);
    },
    getUserById: async (req) => {
        const user = await req.context.db.user.getById(req.params.id);

        if (!user) {
            throw new NotFoundException('user');
        }

        return user;
    },
};
