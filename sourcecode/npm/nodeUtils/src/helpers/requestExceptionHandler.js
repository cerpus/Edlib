import { NotFoundException } from '../exceptions/index.js';

const requestExceptionHandler = (
    fnc,
    { nullOnNotFound } = { nullOnNotFound: false }
) => async (...params) => {
    try {
        return await fnc(...params);
    } catch (e) {
        if (nullOnNotFound && e instanceof NotFoundException) {
            return null;
        }

        throw e;
    }
};

export default requestExceptionHandler;
