import getEnv from '../helpers/getEnv.js';

export default {
    url: getEnv('REACT_APP_API_URL', 'https://api.edlib.local'),
};
