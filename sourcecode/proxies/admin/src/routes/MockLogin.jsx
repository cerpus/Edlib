import React from 'react';
import store from 'store';
import sign from 'jwt-encode';
import {
    Button,
    FormControl,
    FormControlLabel,
    FormLabel,
    Radio,
    RadioGroup,
    TextField,
} from '@material-ui/core';
import useFetch from '../hooks/useFetch.jsx';
import request from '../helpers/request.js';
import storageKeys from '../constants/storageKeys.js';
import AuthContext from '../contexts/auth.js';
import { useHistory } from 'react-router-dom';

const MockLogin = () => {
    const history = useHistory();
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
    const [language, setLanguage] = React.useState(() => {
        const stored = store.get('language');
        return stored ? stored : 'nb';
    });

    React.useEffect(() => {
        store.set('firstName', firstName);
        store.set('lastName', lastName);
        store.set('email', email);
        store.set('userId', id);
        store.set('language', language);
    }, [firstName, lastName, email, id, language]);

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
            <div>
                <FormControl component="fieldset">
                    <FormLabel component="legend">Language</FormLabel>
                    <RadioGroup
                        name="language"
                        value={language}
                        onChange={(e) => setLanguage(e.target.value)}
                    >
                        <FormControlLabel
                            value="nb"
                            control={<Radio />}
                            label="Norsk"
                        />
                        <FormControlLabel
                            value="en"
                            control={<Radio />}
                            label="English"
                        />
                    </RadioGroup>
                </FormControl>
            </div>
            <Button
                color="primary"
                variant="contained"
                onClick={() => {
                    history.push(
                        `/login/callback?jwt=${sign(
                            {
                                exp: Math.floor(Date.now() / 1000) + 60 * 60,
                                data: {
                                    isFakeToken: true,
                                    user: {
                                        firstName:
                                            firstName.length !== 0
                                                ? firstName
                                                : null,
                                        lastName:
                                            lastName.length !== 0
                                                ? lastName
                                                : null,
                                        email:
                                            email.length !== 0 ? email : null,
                                        id,
                                        isAdmin: true,
                                    },
                                },
                                iss: 'fake',
                            },
                            'anything'
                        )}`
                    );
                }}
            >
                Start new session
            </Button>
        </div>
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

export default MockLogin;
