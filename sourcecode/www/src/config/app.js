// eslint-disable-next-line no-restricted-globals
const full = location.protocol + '//' + location.host;

const appConfig = {
    apiUrl: full.replace('www', 'api'),
};

export default appConfig;
