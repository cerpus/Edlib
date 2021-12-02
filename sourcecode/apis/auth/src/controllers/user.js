import { NotFoundException } from '@cerpus/edlib-node-utils';
import appConfig from '../config/app.js';

export default {
    getUsersByEmail: async (req) => {
        return req.context.db.user.getByEmails(req.body.emails);
    },
    getUserById: async (req) => {
        let user;
        if (req.params.id.startsWith(appConfig.ltiUserPrefix)) {
            user = await req.context.db.ltiUser.getById(
                req.params.id.substring(appConfig.ltiUserPrefix.length)
            );
        } else {
            user = await req.context.db.user.getById(req.params.id);
        }

        if (!user) {
            throw new NotFoundException('user');
        }

        return user;
    },
};
