import axios from 'axios';

const instance = axios.create({
  baseURL: 'http://localhost:8000', // your Laravel API URL
  withCredentials: true, // if you're using cookies/session
});

export default instance;
