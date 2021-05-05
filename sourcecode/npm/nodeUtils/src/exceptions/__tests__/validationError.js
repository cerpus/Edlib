import ValidationException, { validationError } from '../validation.js';

describe('Exceptions', () => {
    describe('Validation exception', () => {
        test('validationError returns expected data', () => {
            const e = validationError('key', 'location', 'message');

            expect(e.key).toBe('key');
            expect(e.location).toBe('location');
            expect(e.message).toBe('message');
        });

        describe('Single validation error', () => {
            const validationException = new ValidationException(
                validationError('key', 'location', 'message')
            );

            test('getStatus to return 422', () => {
                expect(validationException.getStatus()).toBe(422);
            });

            test('getBody to return expected data', () => {
                expect(validationException.getBody().type).toBe('validation');
                expect(
                    validationException.getBody().error.messages.length
                ).toBe(1);
                expect(
                    validationException.getBody().error.messages[0].key
                ).toBe('key');
                expect(
                    validationException.getBody().error.messages[0].location
                ).toBe('location');
                expect(
                    validationException.getBody().error.messages[0].message
                ).toBe('message');
            });
        });

        describe('Multiple validation error', () => {
            const validationException = new ValidationException([
                validationError('key', 'location', 'message'),
                validationError('key2', 'location2', 'message2'),
            ]);

            test('getStatus to return 422', () => {
                expect(validationException.getStatus()).toBe(422);
            });

            test('getBody to return expected data', () => {
                expect(validationException.getBody().type).toBe('validation');
                expect(
                    validationException.getBody().error.messages.length
                ).toBe(2);
                expect(
                    validationException.getBody().error.messages[1].key
                ).toBe('key2');
                expect(
                    validationException.getBody().error.messages[1].location
                ).toBe('location2');
                expect(
                    validationException.getBody().error.messages[1].message
                ).toBe('message2');
            });
        });
    });
});
