import React from 'react';
import {
    Box,
    Collapse,
    Paper,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableRow,
    Typography,
} from '@material-ui/core';

const SystemStatus = ({ name, loading, error, data }) => {
    const [isExpanded, setIsExpanded] = React.useState(false);

    if (loading) {
        return (
            <Paper>
                <h2>{name}</h2>
            </Paper>
        );
    }

    if (error) {
        return (
            <Paper>
                <Box display="flex" justifyContent="space-between">
                    <div>{name}</div>
                    <div>
                        <i className="fa fa-caret-down" />
                    </div>
                </Box>
                <Collapse in={isExpanded} timeout="auto">
                    <Typography>
                        Could not get service status. This might be because the
                        service is not set up properly or that you have no
                        internet connection.
                    </Typography>
                </Collapse>
            </Paper>
        );
    }

    return (
        <Paper>
            <Box
                display="flex"
                justifyContent="space-between"
                bgcolor={data.color + '.main'}
                onClick={() => setIsExpanded(!isExpanded)}
            >
                <div>{name}</div>
                <div>
                    <i className="fa fa-caret-down" />
                </div>
            </Box>
            <Collapse in={isExpanded}>
                <Table>
                    <TableHead>
                        <tr>
                            <th width={35} />
                            <th>Subservice name</th>
                            <th>Status</th>
                            <th>Parameters</th>
                        </tr>
                    </TableHead>
                    <TableBody>
                        {data.systems.map((s, index) => (
                            <TableRow key={index}>
                                <TableCell className={'bg-' + s.color} />
                                <TableCell>{s.name}</TableCell>
                                <TableCell>{s.statusMessage}</TableCell>
                                <TableCell>
                                    {s.parameters &&
                                        Object.entries(s.parameters).map(
                                            ([key, value]) => (
                                                <div>
                                                    <strong>{key}: </strong>
                                                    {value}
                                                </div>
                                            )
                                        )}
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </Collapse>
        </Paper>
    );
};

export default SystemStatus;
