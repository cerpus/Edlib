import logRequest from '../logRequest.js';

describe('Middlewares', () => {
    describe('logRequest', () => {
        const next = jest.fn();

        logRequest()(
            { url: 'mock-url', connection: {}, get: () => '' },
            {},
            next
        );

        test('next to be called', () => {
            expect(next).toBeCalled();
        });
    });
});
