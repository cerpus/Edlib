import React from 'react';
import { Button, Container, Grid, TextField } from '@material-ui/core';
import { useArray } from 'moment-hooks';
import useRequestAction from '../../../../hooks/useRequestAction.jsx';
import { useHistory } from 'react-router-dom';
import useRequestWithToken from '../../../../hooks/useRequestWithToken.jsx';

const AuthMigrationContainer = ({ match }) => {
    const history = useHistory();
    const request = useRequestWithToken();
    const [userIds, userIdsActions] = useArray([]);
    const [from, setFrom] = React.useState('');
    const [to, setTo] = React.useState('');
    const [json, setJSON] = React.useState('');
    const [error, setError] = React.useState(null);

    const { status: createAuthMigrationStatus, action: createAuthMigration } =
        useRequestAction((userIds) =>
            request('/common/auth-migrations', 'POST', {
                body: {
                    userIds,
                },
            })
        );

    return (
        <Container>
            <Grid container>
                <Grid item md={12}>
                    <div>
                        <TextField
                            required
                            multiline
                            maxRows={2}
                            label="JSON"
                            value={json}
                            onChange={(e) => setJSON(e.target.value)}
                        />
                        <Button
                            variant="contained"
                            color="primary"
                            onClick={() => {
                                setError(null);
                                try {
                                    const userIds = JSON.parse(json);
                                    if (Array.isArray(userIds)) {
                                        userIds.forEach(({ from, to }) =>
                                            userIdsActions.push({ from, to })
                                        );
                                    }
                                    setJSON('');
                                } catch (e) {
                                    console.error(e);
                                    setError(
                                        'Invalid JSON. Must be an array of objects with keys from and to'
                                    );
                                }
                            }}
                        >
                            Add from JSON
                        </Button>
                    </div>
                    <div>{error}</div>
                </Grid>
                <Grid item md={12}>
                    <TextField
                        required
                        label="From"
                        value={from}
                        onChange={(e) => setFrom(e.target.value)}
                    />
                    <TextField
                        required
                        label="To"
                        value={to}
                        onChange={(e) => setTo(e.target.value)}
                    />
                    <Button
                        variant="contained"
                        color="primary"
                        onClick={() => {
                            if (from !== '' && to !== '') {
                                userIdsActions.push({
                                    from,
                                    to,
                                });
                            }
                        }}
                    >
                        Add
                    </Button>
                </Grid>
                <Grid item md={12}>
                    {userIds.map((userId, index) => (
                        <div key={index}>
                            <strong>From: </strong> {userId.from},{' '}
                            <strong>to: </strong> {userId.to}
                            <Button
                                variant="contained"
                                color="secondary"
                                onClick={() =>
                                    userIdsActions.removeIndex(index)
                                }
                            >
                                Slett
                            </Button>
                        </div>
                    ))}
                </Grid>
                <Grid item md={12}>
                    <Button
                        variant="contained"
                        color="primary"
                        disabled={userIds.length === 0}
                        onClick={() => {
                            createAuthMigration(userIds, (response) =>
                                history.push(`${match.url}/${response.id}`)
                            );
                        }}
                    >
                        Create
                    </Button>
                </Grid>
            </Grid>
        </Container>
    );
};

export default AuthMigrationContainer;
