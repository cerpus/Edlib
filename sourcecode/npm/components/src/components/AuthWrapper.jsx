import React from 'react';
import store from 'store';
import { FormGroup, Input, Label } from '@cerpus/ui';
import request from '../helpers/request';

const AuthWrapper = ({ children, edlibApiUrl }) => {
    const [refreshToken, setRefreshToken] = React.useState(() => {
        const storedToken = store.get('refreshToken');
        return storedToken ? storedToken : '';
    });

    React.useEffect(() => {
        store.set('refreshToken', refreshToken);
    }, [refreshToken]);

    if (!refreshToken) {
        return (
            <FormGroup>
                <Label>Refresh token</Label>
                <Input
                    value={refreshToken}
                    onChange={(e) =>
                        setRefreshToken(e.target.value.replace(/["]+/g, ''))
                    }
                />
            </FormGroup>
        );
    }

    return children({
        getJwt: async () => {
            try {
                const { authToken } = await request(
                    `${edlibApiUrl}/auth/v1/jwt/refresh`,
                    'GET',
                    {
                        query: {
                            refresh_token: refreshToken,
                        },
                    }
                );

                return authToken;
            } catch (e) {
                setRefreshToken(null);
                throw e;
            }
        },
    });
};

export default AuthWrapper;
