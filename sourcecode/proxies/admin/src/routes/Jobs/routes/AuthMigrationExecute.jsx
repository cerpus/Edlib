import React from 'react';
import useFetch from '../../../hooks/useFetch.jsx';
import DefaultHookQuery from '../../../containers/DefaultHookQuery.jsx';
import {
    Button,
    Container,
    Grid,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableRow,
} from '@material-ui/core';
import useRequestAction from '../../../hooks/useRequestAction.jsx';
import request from '../../../helpers/request.js';

const AuthMigrationExecute = ({ match }) => {
    const authMigrationFetchData = useFetch(
        `/common/auth-migrations/${match.params.id}`,
        'GET',
        React.useMemo(() => ({}), [])
    );

    const { status: executeAuthMigrationStatus, action: executeAuthMigration } =
        useRequestAction((userIds) =>
            request(
                `/common/auth-migrations/${match.params.id}/execute`,
                'POST'
            )
        );

    return (
        <Container>
            <DefaultHookQuery fetchData={authMigrationFetchData}>
                {({ response, refetch }) => (
                    <>
                        <Grid container>
                            <Grid item md={12}>
                                Klar: {response.ready ? 'ja' : 'nei'}
                            </Grid>
                            <Grid item md={12}>
                                <Button
                                    variant="contained"
                                    color="primary"
                                    disabled={!response.ready}
                                    onClick={() => executeAuthMigration()}
                                >
                                    Kjør
                                </Button>
                                <Button
                                    variant="contained"
                                    color="primary"
                                    disabled={!response.ready}
                                    onClick={() => refetch()}
                                >
                                    Oppdater siden
                                </Button>
                            </Grid>
                        </Grid>
                        <Grid container>
                            <Grid item md={12}>
                                <h2>Tabeller</h2>
                            </Grid>
                            <Grid item md={12}>
                                <Table>
                                    <TableHead>
                                        <TableRow>
                                            <TableCell>API</TableCell>
                                            <TableCell>Tabell</TableCell>
                                            <TableCell>Antall rader</TableCell>
                                            <TableCell>Ferdig kjørt?</TableCell>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {response.tables.map(
                                            (
                                                {
                                                    apiName,
                                                    tableName,
                                                    rowCount,
                                                    done,
                                                },
                                                index
                                            ) => (
                                                <TableRow key={index}>
                                                    <TableCell>
                                                        {apiName}
                                                    </TableCell>
                                                    <TableCell>
                                                        {tableName}
                                                    </TableCell>
                                                    <TableCell>
                                                        {rowCount}
                                                    </TableCell>
                                                    <TableCell>
                                                        {done ? 'ja' : 'nei'}
                                                    </TableCell>
                                                </TableRow>
                                            )
                                        )}
                                    </TableBody>
                                </Table>
                            </Grid>
                        </Grid>
                        <Grid container>
                            <Grid item md={12}>
                                <h2>Bruker id</h2>
                            </Grid>
                            <Grid item md={12}>
                                <Table>
                                    <TableHead>
                                        <TableRow>
                                            <TableCell>From</TableCell>
                                            <TableCell>To</TableCell>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {response.userIdToChange.map(
                                            ({ from, to }, index) => (
                                                <TableRow key={index}>
                                                    <TableCell>
                                                        {from}
                                                    </TableCell>
                                                    <TableCell>{to}</TableCell>
                                                </TableRow>
                                            )
                                        )}
                                    </TableBody>
                                </Table>
                            </Grid>
                        </Grid>
                    </>
                )}
            </DefaultHookQuery>
        </Container>
    );
};

export default AuthMigrationExecute;
