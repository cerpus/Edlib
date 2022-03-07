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
import useConfig from '../../hooks/useConfig.js';
import { Box, CircularProgress } from '@mui/material';
import moment from 'moment';
import useTranslation from '../../hooks/useTranslation.js';

const ResourceStats = ({ resourceId }) => {
    const { t } = useTranslation();
    const { edlib } = useConfig();

    const { loading, response } = useFetchWithToken(
        edlib(`/resources/v1/resources/${resourceId}/stats`)
    );

    if (loading || !response) {
        return <CircularProgress />;
    }
    const last7daysViews = response.data.last7daysViews;
    const datasets = [
        {
            key: 'count',
            name: _.capitalize(t('resource_view', { count: 2 })),
            dataset: last7daysViews,
        },
    ];

    return (
        <>
            <Box pb={2}>
                <strong>{t('S.VIEWS_PAST_7_DAYS')}</strong>
            </Box>
            <ResponsiveContainer width="100%" height={100}>
                <LineChart
                    data={fillEmptyDays(
                        last7daysViews,
                        moment().subtract(7, 'days').startOf('day'),
                        moment().endOf('day'),
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
        </>
    );
};

export default ResourceStats;
