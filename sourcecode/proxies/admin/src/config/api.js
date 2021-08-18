import getEnv from '../helpers/getEnv.js';

export default {
    url: getEnv('REACT_APP_API_URL', 'https://api.edlib.local'),
    showMockLogin: getEnv('REACT_APP_SHOW_MOCK_LOGIN', 'false') === 'true',
};
