import Axios from 'axios';

const accesstoken = document.getElementsByTagName('meta').namedItem('access-token');
if (accesstoken !== null) {
    Axios.defaults.headers.common.Authorization = 'Bearer ' + accesstoken.getAttribute('content');
}

export default Axios;
