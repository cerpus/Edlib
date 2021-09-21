import addContextToRequest from '../addContextToRequest.js';

describe('Middlewares', () => {
    describe('addContextToRequest', () => {
        test('context is present', () => {
            let req = {};
            const next = jest.fn();
            addContextToRequest({})(req, {}, next);

            expect(req.context).toBeDefined();
        });

        test('next to be called', () => {
            let req = {};
            const next = jest.fn();
            addContextToRequest({})(req, {}, next);

            expect(next).toBeCalled();
        });
    });
});
