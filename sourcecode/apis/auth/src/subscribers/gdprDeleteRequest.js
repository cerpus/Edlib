import { buildRawContext } from '../context/index.js';

export default ({ pubSubConnection }) => async (data) => {
    const context = buildRawContext({}, {}, { pubSubConnection });

    const user = await context.db.user.getById(data.userId);

    if (user) {
        await context.db.user.update(user.id, {
            firstName: data.userDataForOverride.firstName,
            lastName: data.userDataForOverride.lastName,
            email: data.userDataForOverride.email,
        });
    }
};
