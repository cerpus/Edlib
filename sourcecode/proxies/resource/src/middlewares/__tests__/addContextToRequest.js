import addContextToRequest from '../addContextToRequest.js';

describe('Middlewares', () => {
    describe('addContextToRequest', () => {
        let req = { headers: {}, cookies: {}, query: {} };
        const next = jest.fn();
        addContextToRequest(req, {}, next);

        test('context is present', () => {
            expect(req.context).toBeDefined();
        });

        test('next to be called', () => {
            expect(next).toBeCalled();
        });
    });
});
