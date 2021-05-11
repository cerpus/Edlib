import React from 'react';
import store from 'store';
import { FormGroup, Input, Label } from '@cerpus/ui';
import { isTokenExpired } from '../helpers/token.js';

const AuthWrapper = ({ children }) => {
    const [jwtToken, setJwtToken] = React.useState(() => {
        const storedToken = store.get('jwtToken');
        return storedToken ? storedToken : '';
    });

    React.useEffect(() => {
        store.set('jwtToken', jwtToken);
    }, [jwtToken]);

    if (!jwtToken) {
        return (
            <FormGroup>
                <Label>Refresh token</Label>
                <Input
                    value={jwtToken}
                    onChange={(e) =>
                        setJwtToken(e.target.value.replace(/["]+/g, ''))
                    }
                />
            </FormGroup>
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
