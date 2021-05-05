import JsonWebToken from 'jsonwebtoken';
import jwtConfig from '../envConfig/jwt.js';

export const encrypt = (payload, expireHours = 72) =>
    JsonWebToken.sign(
        {
            payload,
            exp: Math.floor(Date.now() / 1000) + 60 * 60 * expireHours,
        },
        jwtConfig.secret
    );

export const verify = (token, options) =>
    JsonWebToken.verify(token, jwtConfig.secret, options);
