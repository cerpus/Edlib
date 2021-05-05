import NotFoundException from '../notFound.js';

describe('Exceptions', () => {
    describe('not found', () => {
        const notFound = new NotFoundException('user');

        test('getStatus to return 404', () => {
            expect(notFound.getStatus()).toBe(404);
        });

        test('get body to return not found parameter in response', () => {
            expect(notFound.getBody().error.parameter).toBe('user');
        });
    });
});
