import React from 'react';
import {
    Breadcrumbs,
    Box,
    Container,
    Grid,
    Typography,
    Paper,
    Table,
    TableHead,
    TableRow,
    TableCell,
    TableBody,
    Button,
} from '@material-ui/core';
import { Delete } from '@material-ui/icons';
import { Alert } from '@material-ui/lab';
import Link from '../../../../../../components/Link';
import DefaultHookQuery from '../../../../../../containers/DefaultHookQuery';

const ExternalApplicationDetails = ({
    applicationTokensFetchData,
    onCreate,
    createStatus,
    token,
    application,
    onDeleteRequest,
}) => {
    return (
        <Container maxWidth={false}>
            <Grid component={Box} container paddingY={2}>
                <Grid item>
                    <Breadcrumbs aria-label="breadcrumb">
                        <Link to="/">Edlib admin</Link>
                        <Link to="/settings">Settings</Link>
                        <Link to="/settings/external-applications">
                            External applications
                        </Link>
                        <Typography color="textPrimary">
                            {application.name}
                        </Typography>
                    </Breadcrumbs>
                </Grid>
            </Grid>
            <Grid container component={Box} paddingBottom={2}>
                <Grid item md={12}>
                    <Typography variant="h2">{application.name}</Typography>
                </Grid>
            </Grid>
            <Grid container>
                <Grid item md={6}>
                    <Paper>
                        <Box padding={2}>
                            <strong>Application ID: </strong> {application.id}
                        </Box>
                        <Box padding={2}>
                            <Button
                                variant="contained"
                                color="primary"
                                onClick={() => onCreate()}
                                disabled={createStatus.loading}
                            >
                                Create new
                            </Button>
                        </Box>
                        {token && (
                            <Alert>Created new access token: {token}</Alert>
                        )}
                        <DefaultHookQuery
                            fetchData={applicationTokensFetchData}
                        >
                            {({ response: accessTokens }) => (
                                <Table>
                                    <TableHead>
                                        <TableRow>
                                            <TableCell>Id</TableCell>
                                            <TableCell>Name</TableCell>
                                            <TableCell>Delete</TableCell>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {accessTokens.map(({ id, name }) => (
                                            <TableRow key={id}>
                                                <TableCell width={260}>
                                                    {id}
                                                </TableCell>
                                                <TableCell>{name}</TableCell>
                                                <TableCell>
                                                    <Button
                                                        startIcon={<Delete />}
                                                        color="error"
                                                        onClick={() =>
                                                            onDeleteRequest(id)
                                                        }
                                                    >
                                                        Delete
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            )}
                        </DefaultHookQuery>
                    </Paper>
                </Grid>
            </Grid>
        </Container>
    );
};

export default ExternalApplicationDetails;
