import React from 'react';
import store from 'store';
import sign from 'jwt-encode';
import { isTokenExpired } from '../helpers/token.js';
import {
    Button,
    FormControl,
    InputLabel,
    MenuItem,
    Select,
    TextField,
} from '@material-ui/core';
import i18n from '../i18n';

const AuthWrapper = ({ children }) => {
    const [jwtToken, setJwtToken] = React.useState(null);
    const languages = Object.keys(i18n.options.resources);

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
        const fallback = i18n.options.fallbackLng[0] && languages.includes(i18n.options.fallbackLng[0]) ?
            i18n.options.fallbackLng[0] :
            languages[0] ?? '';
        return stored && languages.includes(stored) ? stored : fallback;
    });

    React.useEffect(() => {
        store.set('firstName', firstName);
        store.set('lastName', lastName);
        store.set('email', email);
        store.set('userId', id);
        store.set('language', language);
    }, [firstName, lastName, email, id, language]);

    const [firstNameErrorText, setFirstNameErrorText] = React.useState("");
    const [lastNameErrorText, setLastNameErrorText] = React.useState("");
    const [emailErrorText, setEmailErrorText] = React.useState("");
    const [idErrorText, setIdErrorText] = React.useState("");
    const isValidEmail = (email) => {
        return /^[^@]+@([^@.]+\.)*[^@.]+$/.test(email);
    }
    const isValidUser = (userId) => {
        return !isNaN(userId) && userId;
    }

    if (!jwtToken) {
        return (
            <div style={{ maxWidth: 500 }}>
                <div>
                    <TextField
                        label="First name"
                        value={firstName}
                        onChange={(e) => setFirstName(e.target.value)}
                        margin="normal"
                        style={{marginRight: 10}}
                        error={firstNameErrorText !== ''}
                        helperText={firstNameErrorText}
                    />
                    <TextField
                        label="Last name"
                        value={lastName}
                        onChange={(e) => setLastName(e.target.value)}
                        margin="normal"
                        error={lastNameErrorText !== ''}
                        helperText={lastNameErrorText}
                    />
                </div>
                <div>
                    <TextField
                        label="Email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        margin="normal"
                        fullWidth
                        error={emailErrorText !== ''}
                        helperText={emailErrorText}
                    />
                </div>
                <div>
                    <TextField
                        label="User ID"
                        value={id}
                        onChange={(e) => setId(e.target.value)}
                        margin="normal"
                        fullWidth
                        error={idErrorText !== ''}
                        helperText={idErrorText}
                    />
                </div>
                <div style={{marginTop: 20}}>
                    <FormControl component="fieldset">
                        <InputLabel id="language-input-label">Language</InputLabel>
                        <Select
                            labelId="language-input-label"
                            id="language-input"
                            value={language}
                            onChange={(e) => setLanguage(e.target.value)}
                        >
                            {languages.map(lng =>
                                <MenuItem
                                    key={lng}
                                    value={lng}
                                >
                                    {lng}
                                </MenuItem>
                            )}
                        </Select>
                    </FormControl>
                </div>
                <div style={{marginTop: 40}}>
                    <Button
                    type="submit"
                        color="primary"
                        variant="contained"
                        style={{textTransform: 'none'}}
                        onClick={() => {

                            let error = false;
                            if (!firstName) {
                                setFirstNameErrorText("Please enter first name");
                                error = true;
                            }else {
                                setFirstNameErrorText("");
                            }

                            if (!lastName) {
                                setLastNameErrorText("Please enter last name");
                                error = true;
                            }else {
                                setLastNameErrorText("");
                            }
                            
                            if (!email) {
                                setEmailErrorText("Please enter email");
                                error = true;
                            }else if (email && !isValidEmail(email)) {
                                setEmailErrorText("Invalid email address");
                                error = true;
                            }else {
                                setEmailErrorText("");
                            }
                            
                            if(!isValidUser(id)) {
                                setIdErrorText("Please enter a valid numeric user id");
                                error = true;
                            }else {
                                setIdErrorText("");
                            }
                            
                            if(error){
                                return;
                            }

                            setJwtToken(
                                sign(
                                    {
                                        exp:
                                            Math.floor(Date.now() / 1000) + 60 * 60,
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
                                                    email.length !== 0
                                                        ? email
                                                        : null,
                                                id,
                                                isAdmin: true,
                                            },
                                        },
                                        iss: 'fake',
                                    },
                                    'anything'
                                )
                            );
                        }}
                    >
                        Start new session
                    </Button>
                </div>
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
            return language;
        },
    });
};

export default AuthWrapper;
