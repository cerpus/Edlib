import React from 'react';
import { Box, Container, Grid, Paper } from '@material-ui/core';
import {
    CartesianGrid,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { fillEmptyDays } from '../../../../helpers/chart';

const Dashboard = ({ viewsByDay, from, to }) => {
    const datasets = [
        {
            key: 'count',
            name: 'Ressursvisninger',
            dataset: viewsByDay,
        },
    ];
    return (
        <Container>
            <Grid container>
                <Grid item md={12}>
                    <Box component={Paper} padding={2} marginBottom={1}>
                        <Box marginBottom={2}>
                            <strong>Ressurs over tid</strong>
                        </Box>
                        <ResponsiveContainer width="100%" height={200}>
                            <LineChart
                                data={fillEmptyDays(viewsByDay, from, to, {
                                    zeroFields: datasets.map(
                                        (dataset) => dataset.key
                                    ),
                                })}
                                margin={{
                                    left: -20,
                                }}
                            >
                                <XAxis dataKey="date" />
                                <YAxis />
                                <Tooltip />
                                <CartesianGrid
                                    stroke="#f5f5f5"
                                    vertical={false}
                                />
                                {datasets.map((dataset) => (
                                    <Line
                                        name={dataset.name}
                                        type="monotone"
                                        dataKey={dataset.key}
                                        stroke={dataset.color}
                                        yAxisId={0}
                                    />
                                ))}
                            </LineChart>
                        </ResponsiveContainer>
                    </Box>
                </Grid>
            </Grid>
        </Container>
    );
};

export default Dashboard;
