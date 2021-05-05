import validate from '../validate.js';
import Joi from '@hapi/joi';
import ValidationException from '../../exceptions/validation.js';

describe('Middlewares', () => {
    describe('validate', () => {
        test('next to be called', () => {
            const next = jest.fn();

            validate(
                Joi.object().keys({
                    someKey: Joi.string().required(),
                })
            )({ body: { someKey: 'test' } }, {}, next);

            expect(next).toBeCalled();
        });

        test('validation exception to be thrown', () => {
            const next = jest.fn();

            expect(() => {
                validate(
                    Joi.object().keys({
                        someKey: Joi.string().required(),
                    })
                )({ body: { not: 'test' } }, {}, next);
            }).toThrow(ValidationException);
        });
    });
});
