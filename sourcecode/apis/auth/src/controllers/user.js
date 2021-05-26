export default {
    getUsersByEmail: async (req) => {
        return req.context.db.user.getByEmails(req.body.emails);
    },
};
