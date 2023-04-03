import React, {useState} from 'react';
import _ from 'lodash';
import {
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { makeStyles } from 'tss-react/mui';
import { fillEmptyDays } from '../../helpers/chart.js';
import useFetchWithToken from '../../hooks/useFetchWithToken.jsx';
import { Box, CircularProgress } from '@mui/material';
import moment from 'moment';
import useTranslation from '../../hooks/useTranslation.js';
import { useConfigurationContext } from '../../contexts/Configuration.jsx';

const useStyles = makeStyles()((theme) => ({
    views: {
        fontSize: '1rem',
        fontWeight: '400 !important',
    },
    totalViews: {
        fontSize: '1rem',
        fontWeight: '400 !important',
    },
    dates: {
        fontSize: '1rem',
        fontWeight: '400 !important',
    },
    totalCount: {
        fontSize: '1rem',
        fontWeight: '400 !important',
    },
}));
const ResourceStats = ({ resourceId }) => {
    const { classes } = useStyles();
    const { t } = useTranslation();
    const { edlibApi } = useConfigurationContext();

    const today = moment(new Date()).format('YYYY-MM-DD');
    const lastMonth = moment(today).subtract(30, 'days').format('YYYY-MM-DD');

    const [startDate, setStartDate] = useState(lastMonth);
    const [endDate, setEndDate] = useState(today);

    const {loading, response} = useFetchWithToken(
        edlibApi(`/resources/v1/resources/${resourceId}/stats?start=${startDate}&end=${endDate}`)
    );
    const dateRangeViews = response?.data?.dateRangeViews || {};
    const datasets = [
        {
            key: 'count',
            name: _.capitalize(t('resource_view', { count: 2 })),
            dataset: dateRangeViews,
        },
    ];
    const handleDateChange = (event) => {
        setStartDate(event.target.value);
    };
    const toDateChange = (event) => {
        setEndDate(event.target.value);
    };
    let viewsCount = 0;
    for (let i = 0; i < response?.data?.dateRangeViews?.length; i++) {
        viewsCount = viewsCount + response?.data?.dateRangeViews[i].count;
    }
    return (
        <>
            <Box pb={2} className={classes.views}>
                {t('S.VIEWS')}
            </Box>
            <Box pb={2} className={classes.dates}>
                <label htmlFor="from"> {t('S.FROM')}: <input type="date" id="from" max={endDate}
                                                    onChange={handleDateChange}
                                                    value={startDate}/> </label> {' '}
                <label htmlFor="to">{t('S.TO')}: <input type="date" id="to"
                                               min={startDate}
                                               max={new Date()} onChange={toDateChange}
                                               value={endDate}/></label>
            </Box>
            {loading || !response ?
                <CircularProgress/>
                :
                <ResponsiveContainer width="100%" height={100}>
                    <LineChart
                        data={fillEmptyDays(
                            dateRangeViews,
                            moment(startDate).startOf('day'),
                            moment(endDate).endOf('day'),
                            {
                                zeroFields: datasets.map((dataset) => dataset.key),
                            }
                        )}
                        margin={{
                            left: -20,
                        }}
                    >
                        <XAxis dataKey="date" tick={{fontSize: '0.875rem'}} />
                        <YAxis allowDecimals={false} tick={{fontSize: '0.875rem'}} />
                        <Tooltip />
                        {datasets.map((dataset) => (
                            <Line
                                key={dataset.name}
                                dot={false}
                                name={dataset.name}
                                type="monotone"
                                dataKey={dataset.key}
                                stroke={dataset.color}
                                yAxisId={0}
                                isAnimationActive={false}
                            />
                        ))}
                    </LineChart>
                </ResponsiveContainer>
            }
            <Box pb={2} className={classes.totalCount}>
                {t('S.RANGE_VIEWS')}: {viewsCount}
            </Box>
            <Box pb={2} className={classes.totalViews}>
                {t('S.TOTAL_VIEWS')}: {response?.data?.totalViews}
            </Box>
        </>
    );
};

export default ResourceStats;

