import axios from 'axios';
window.axios = axios;
window.axios.defaults.common['X-Requested-With'] = 'XMLHttpRequest';
