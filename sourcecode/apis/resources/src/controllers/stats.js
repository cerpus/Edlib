import moment from 'moment';

export default {
    getResourceVersionEventsByDay: async (req) => {
        return {
            data: await req.context.db.trackingResourceVersion.getCountByDay(
                moment().subtract(7, 'days').startOf('day').toDate(),
                moment().endOf('day').toDate()
            ),
        };
    },
};
