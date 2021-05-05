import endpointNotFoundHandler from '../endpointNotFoundHandler.js';
import EndpointNotFoundException from '../../exceptions/endpointNotFound.js';

describe('Middlewares', () => {
    describe('endpointNotFoundHandler', () => {
        const next = jest.fn();

        endpointNotFoundHandler({}, {}, next);

        test('next to be called with EndpointNotFoundException', () => {
            expect(next.mock.calls[0][0]).toBeInstanceOf(
                EndpointNotFoundException
            );
        });
    });
});
