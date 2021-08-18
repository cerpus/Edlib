import React from 'react';
import store from 'store';
import storageKeys from '../../constants/storageKeys.js';
import request from '../../helpers/request.js';
import AuthContext from '../../contexts/auth.js';
import { Button, TextField } from '@material-ui/core';
import jwt from 'jsonwebtoken';
import useFetch from '../../hooks/useFetch.jsx';

const MockProviderContainer = ({ children }) => {
    const [jwtToken, setJwtToken] = React.useState(null);

    const [firstName, setFirstName] = React.useState(() => {
        const stored = store.get('firstName');
        return stored ? stored : '';
    });
    const [lastName, setLastName] = React.useState(() => {
        const stored = store.get('lastName');
        return stored ? stored : '';
    });
    const [email, setEmail] = React.useState(() => {
        const stored = store.get('email');
        return stored ? stored : '';
    });
    const [id, setId] = React.useState(() => {
        const stored = store.get('userId');
        return stored ? stored : '';
    });

    React.useEffect(() => {
        store.set('firstName', firstName);
        store.set('lastName', lastName);
        store.set('email', email);
        store.set('userId', id);
    }, [firstName, lastName, email, id]);

    if (!jwtToken) {
        return (
            <div style={{ maxWidth: 500 }}>
                <div>
                    <TextField
                        label="First name"
                        value={firstName}
                        onChange={(e) => setFirstName(e.target.value)}
                        margin="normal"
                    />
                    <TextField
                        label="Last name"
                        value={lastName}
                        onChange={(e) => setLastName(e.target.value)}
                        margin="normal"
                    />
                </div>
                <div>
                    <TextField
                        label="Email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        margin="normal"
                        fullWidth
                    />
                    <TextField
                        label="User ID"
                        value={id}
                        onChange={(e) => setId(e.target.value)}
                        margin="normal"
                        fullWidth
                    />
                </div>
                <Button
                    color="primary"
                    variant="contained"
                    onClick={() => {
                        request('/auth/v1/jwt/convert', 'POST', {
                            body: {
                                externalToken: jwt.sign(
                                    {
                                        exp:
                                            Math.floor(Date.now() / 1000) +
                                            60 * 60,
                                        data: {
                                            isFakeToken: true,
                                            user: {
                                                firstName,
                                                lastName,
                                                email,
                                                id,
                                                isAdmin: true,
                                            },
                                        },
                                    },
                                    'anything'
                                ),
                            },
                        }).then((response) => {
                            setJwtToken(response.token);
                            store.set(storageKeys.AUTH_TOKEN, response.token);
                        });
                    }}
                >
                    Start new session
                </Button>
            </div>
        );
    }

    return (
        <MockProvider jwtToken={jwtToken} logout={() => setJwtToken(null)}>
            {children}
        </MockProvider>
    );
};

const MockProvider = ({ children, logout }) => {
    const fetch = async () => {};
    const { loading, response: user, setResponse } = useFetch('/auth/v1/me');

    React.useEffect(() => {
        if (!user) {
            return;
        }

        const intervalId = setInterval(() => {
            request('/auth/v3/jwt/refresh', 'POST')
                .then(({ token }) => {
                    store.set(storageKeys.AUTH_TOKEN, token);
                })
                .catch(() => {
                    setResponse(null);
                });
        }, 1000 * 60);

        return () => clearInterval(intervalId);
    }, [user]);

    return (
        <AuthContext.Provider
            value={{
                isAuthenticated: !!user,
                isAuthenticating: loading,
                user,
                refetch: fetch,
                logout: () => {
                    setResponse(null);
                    store.remove(storageKeys.AUTH_TOKEN);
                    logout();
                },
                login: ({ user, token }) => {
                    setResponse(user);
                    store.set(storageKeys.AUTH_TOKEN, token);
                },
                loginUrl: '',
            }}
        >
            {children}
        </AuthContext.Provider>
    );
};

export default MockProviderContainer;
