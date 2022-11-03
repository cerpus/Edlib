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
                dateRangeViews:
                    await req.context.db.trackingResourceVersion.getCountByDayForResource(
                        moment(req.query.start).startOf('day').toDate(),
                        moment(req.query.end).endOf('day').toDate(),
                        req.params.resourceId
                    ),
            },
        };
    },
};
