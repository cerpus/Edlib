import exceptionHandler from '../exceptionHandler.js';
import Validation, { validationError } from '../../exceptions/validation.js';

const mockRes = () => {
    let res = {};

    res.status = jest.fn().mockReturnValue(res);
    res.json = jest.fn().mockReturnValue(res);
    res.render = jest.fn().mockReturnValue(res);

    return res;
};

describe('Middlewares', () => {
    describe('exceptionHandler', () => {
        describe('server error', () => {
            const next = jest.fn();
            const res = mockRes();

            exceptionHandler(
                {},
                { is: () => false, accepts: () => null },
                res,
                next
            );

            test('next to not be called', () => {
                expect(next).not.toBeCalled();
            });

            test('status to be called', () => {
                expect(res.status).toBeCalled();
            });

            test('json to be called', () => {
                expect(res.render).toBeCalled();
            });

            test('status to be 500', () => {
                expect(res.status.mock.calls[0][0]).toBe(500);
            });
        });

        describe('validation error', () => {
            const next = jest.fn();
            const res = mockRes();

            exceptionHandler(
                new Validation(validationError('key', 'body', 'test')),
                { is: () => false, accepts: () => null },
                res,
                next
            );

            test('next to not be called', () => {
                expect(next).not.toBeCalled();
            });

            test('status to be called', () => {
                expect(res.status).toBeCalled();
            });

            test('json to be called', () => {
                expect(res.render).toBeCalled();
            });

            test('status to be 422', () => {
                expect(res.status.mock.calls[0][0]).toBe(422);
            });
        });
    });
});
