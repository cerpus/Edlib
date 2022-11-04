import React from 'react';
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
    return (
        <>
            <Box pb={2}>
                <strong>{t('S.VIEWS')}</strong>
            </Box>
            <Box pb={2}>
                <label htmlFor="from"> From: <input type="date" id="from" max={endDate}
                                                    onChange={handleDateChange}
                                                    value={startDate}/> </label> {' '}
                <label htmlFor="to">To: <input type="date" id="to"
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
                        <XAxis dataKey="date" />
                        <YAxis allowDecimals={false} />
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
            <Box pb={2}>
                <strong>{t('S.TOTAL_VIEWS')} : {response?.data?.dateRangeViews?.length} </strong>
            </Box>
        </>
    );
};

export default ResourceStats;

