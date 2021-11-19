import React from 'react';
import store from 'store';
import sign from 'jwt-encode';
import { isTokenExpired } from '../helpers/token.js';
import { Button, FormControl, FormControlLabel, FormLabel, Radio, RadioGroup, TextField } from '@material-ui/core';

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
    const [language, setLanguage] = React.useState(() => {
        const stored = store.get('language');
        return stored ? stored : 'nb';
    });

    React.useEffect(() => {
        store.set('firstName', firstName);
        store.set('lastName', lastName);
        store.set('email', email);
        store.set('userId', id);
        store.set('language', language)
    }, [firstName, lastName, email, id, language]);

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
                <div>
                    <FormControl component="fieldset">
                        <FormLabel component="legend">Language</FormLabel>
                        <RadioGroup
                            name="language"
                            value={language}
                            onChange={e => setLanguage(e.target.value)}
                        >
                            <FormControlLabel value="nb" control={<Radio />} label="Norsk" />
                            <FormControlLabel value="en" control={<Radio />} label="English" />
                        </RadioGroup>
                    </FormControl>
                </div>
                <Button
                    color="primary"
                    variant="contained"
                    onClick={() => {
                        console.log('create token');
                        setJwtToken(
                            sign(
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
        getLanguage: () => {
            console.log(language);
            return language;
        },
    });
};

export default AuthWrapper;
