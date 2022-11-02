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
import { fillEmptyDays } from '../../helpers/chart.js';
import useFetchWithToken from '../../hooks/useFetchWithToken.jsx';
import { Box, CircularProgress } from '@mui/material';
import moment from 'moment';
import useTranslation from '../../hooks/useTranslation.js';
import { useConfigurationContext } from '../../contexts/Configuration.jsx';

const ResourceStats = ({ resourceId }) => {
    const { t } = useTranslation();
    const { edlibApi } = useConfigurationContext();

    const today = moment(new Date()).format('YYYY-MM-DD');
    const lastWeek = moment(today).subtract(7, 'days').format('YYYY-MM-DD');

    const [startDate, setStartDate] = useState(lastWeek);
    const [endDate, setEndDate] = useState(today);

    const { loading, response } = useFetchWithToken(
       edlibApi(`/resources/v1/resources/${resourceId}/stats?start=${startDate}&end=${endDate}`)
    );

    const dateRangeViews = response?.data?.dateRangeViews || {} ;
    const datasets = [
        {
            key: 'count',
            name: _.capitalize(t('resource_view', { count: 2 })),
            dataset: dateRangeViews,
        },
    ];

    const fromDate = moment(startDate);
    const toDate = moment(endDate);
    const diffDays = toDate.diff(fromDate, 'days');

    const handleDateChange = (event) => {
        setStartDate(event.target.value);
    };
    const toDateChange = (event) => {
        setEndDate(event.target.value);
    };

    return (
        <>
            <Box pb={2}>
                <strong>{t('S.VIEWS')}</strong>
            </Box>

            <Box pb={2}>
            From: <input type="date" id="from" name="from" max={endDate} onChange={handleDateChange} value={moment(startDate).format('YYYY-MM-DD')} /> {' '}
            To: <input type="date" id="to" name="to" min={moment(startDate).format('YYYY-MM-DD')} max={moment(new Date()).format('YYYY-MM-DD')} onChange={toDateChange} value={moment(endDate).format('YYYY-MM-DD')}/>
            </Box>

            {loading || !response ?
                <CircularProgress/>
                :
                <ResponsiveContainer width="100%" height={100}>
                    <LineChart
                        data={fillEmptyDays(
                            dateRangeViews,
                            moment().subtract(diffDays, 'days').startOf('day'),
                            moment().endOf('day'),
                            {
                                zeroFields: datasets.map((dataset) => dataset.key),
                            }
                        )}
                        margin={{
                            left: -20,
                        }}
                    >
                        <XAxis dataKey="date"/>
                        <YAxis allowDecimals={false}/>
                        <Tooltip/>
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
            <Box pb={2}>
                <strong>{t('S.TOTAL_VIEWS')} : {response?.data?.dateRangeViews?.length} </strong>
            </Box>

        </>
    );
};

export default ResourceStats;