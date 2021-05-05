import ApiException from '../apiException.js';

describe('Exceptions', () => {
    describe('Api exception', () => {
        describe('Initialized with no status code', () => {
            const apiException = new ApiException('message');

            test('getStatus to return 500', () => {
                expect(apiException.getStatus()).toBe(500);
            });

            test('getBody to return expected data', () => {
                expect(apiException.getBody().type).toBe('server_error');
                expect(apiException.getBody().message).toBe('message');
            });
        });
        describe('Initialized with no status code', () => {
            const apiException = new ApiException('message', 501);

            test('getStatus to return 500', () => {
                expect(apiException.getStatus()).toBe(501);
            });

            test('getBody to return expected data', () => {
                expect(apiException.getBody().type).toBe('server_error');
                expect(apiException.getBody().message).toBe('message');
            });
        });
    });
});
