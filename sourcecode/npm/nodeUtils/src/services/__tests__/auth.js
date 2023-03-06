import { getKeyFromAuth, verifyTokenAgainstAuth } from '../auth';
import createJWKSMock from 'mock-jwks';

describe('apiClients', () => {
    describe('edlibAuth', () => {
        describe('verifyTokenAgainstAuth', () => {
            test('To return payload if valid token is passed', async () => {
                const url = new URL(
                    'http://www.test.com/.well-known/jwks.json'
                );
                const jwksMock = createJWKSMock(
                    `${url.protocol}//${url.host}`,
                    url.pathname
                );
                const payload = {
                    payload: 'smth',
                    iat: 1234567890,
                };

                jwksMock.start();
                const token = jwksMock.token(payload);

                expect(
                    await verifyTokenAgainstAuth({}, url.toString())(token)
                ).toStrictEqual(payload);

                await jwksMock.stop();
            });
            test('To return null on invalid token', async () => {
                const invalidToken = () => {
                    const url = new URL(
                        'http://www.test2.com/.well-known/jwks.json'
                    );
                    const jwksMock = createJWKSMock(
                        `${url.protocol}//${url.host}`,
                        url.pathname
                    );

                    return jwksMock.token({
                        payload: 'smth',
                    });
                };

                const url = new URL(
                    'http://www.test.com/.well-known/jwks.json'
                );
                const jwksMock = createJWKSMock(
                    `${url.protocol}//${url.host}`,
                    url.pathname
                );

                jwksMock.start();

                expect(
                    await verifyTokenAgainstAuth(
                        {},
                        url.toString()
                    )(invalidToken())
                ).toStrictEqual(null);

                await jwksMock.stop();
            });
        });
    });
});
