import getEnv from '../helpers/getEnv.js';

const apiUrl = `https://api.${getEnv('REACT_APP_EDLIB_ROOT_DOMAIN', 'edlib.test')}`;

const apiConfig = {
    wwwUrl: getEnv('REACT_APP_API_URL', apiUrl).replace('api', 'www'),
    contentauthorUrl: getEnv('REACT_APP_API_URL', apiUrl).replace('api', 'ca'),
    url: getEnv('REACT_APP_API_URL', apiUrl),
    showMockLogin: getEnv('REACT_APP_SHOW_MOCK_LOGIN', 'false') === 'true',
};

export default apiConfig;
