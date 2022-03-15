import hasJwt from '../hasJwt.js';
import { UnauthorizedException } from '@cerpus/edlib-node-utils';

describe('Middlewares', () => {
    describe('hasJwt', () => {
        test('next to be called', () => {
            const next = jest.fn();
            const req = {
                headers: {
                    authorization: 'Bearer test',
                },
                cookies: {},
                query: {},
            };

            hasJwt(req, {}, next);

            expect(next).toBeCalled();
            expect(req.authorizationJwt).toBeDefined();
        });

        test('Unauthorized exception to be thrown if no token', () => {
            const next = jest.fn();
            const req = {
                headers: {},
                cookies: {},
                query: {},
            };

            expect(() => {
                hasJwt(req, {}, next);
            }).toThrow(UnauthorizedException);
        });

        test('Unauthorized exception to be thrown if invalid token', () => {
            const next = jest.fn();
            const req = {
                headers: {
                    authorization: 'asd',
                },
                cookies: {},
                query: {},
            };

            expect(() => {
                hasJwt(req, {}, next);
            }).toThrow(UnauthorizedException);
        });
    });
});
