import React from 'react';
import store from 'store';
import jwt from 'jsonwebtoken';
import { isTokenExpired } from '../helpers/token.js';
import { Button, TextField } from '@material-ui/core';

const AuthWrapper = ({ children }) => {
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
                        console.log('create token');
                        setJwtToken(
                            jwt.sign(
                                {
                                    exp:
                                        Math.floor(Date.now() / 1000) + 60 * 60,
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
                            )
                        );
                    }}
                >
                    Start new session
                </Button>
            </div>
        );
    }

    return children({
        getJwt: async () => {
            if (jwtToken && isTokenExpired(jwtToken)) {
                setJwtToken(null);
                return null;
            }

            return jwtToken;
        },
    });
};

export default AuthWrapper;
