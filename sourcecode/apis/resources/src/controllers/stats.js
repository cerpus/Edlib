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
    getResourceStats: async (req) => {
        return {
            data: {
                last7daysViews:
                    await req.context.db.trackingResourceVersion.getCountByDayForResource(
                        moment().subtract(7, 'days').startOf('day').toDate(),
                        moment().endOf('day').toDate(),
                        req.params.resourceId
                    ),
            },
        };
    },
};
