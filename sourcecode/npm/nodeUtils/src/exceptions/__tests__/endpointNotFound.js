import EndpointNotFoundException from '../endpointNotFound.js';

describe('Exceptions', () => {
    describe('Endpoint not found', () => {
        const endpointNotFound = new EndpointNotFoundException('/path', 'GET');

        test('getStatus to return 404', () => {
            expect(endpointNotFound.getStatus()).toBe(404);
        });

        test('getBody to return expected data', () => {
            expect(endpointNotFound.getBody().type).toBe('endpoint_not_found');
            expect(endpointNotFound.getBody().error.path).toBe('/path');
            expect(endpointNotFound.getBody().error.method).toBe('GET');
        });
    });
});
