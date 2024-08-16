import { AxiosException } from '../exceptions/index.js';
import logger from '../services/logger.js';

const delay = (time) => new Promise((resolve) => setTimeout(resolve, time));

const autoRetryRequest = (
    fnc,
    options = {
        coolDownTime: 5000,
        numberOfRetries: 3,
    },
    iteration = 0
) => async (...params) => {
    try {
        return await fnc(...params);
    } catch (e) {
        if (
            iteration < options.numberOfRetries &&
            e instanceof AxiosException &&
            e.getAxiosResponseStatus() >= 500
        ) {
            logger.info(
                'Request failed. Retrying after cooldown of ' +
                    options.coolDownTime
            );
            await delay(options.coolDownTime);

            return autoRetryRequest(fnc, options, iteration + 1)(...params);
        }

        throw e;
    }
};

export default autoRetryRequest;
