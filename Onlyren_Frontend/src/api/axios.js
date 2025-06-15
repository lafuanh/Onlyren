// in api/axios
import axios from 'axios';

const instance = axios.create({
  baseURL: 'https://onlyren.noupal.pro/api/', // your Laravel API URL
  withCredentials: true, // if you're using cookies/session
});

export default instance;