import axios from './axios';

export const login = (email, password) => axios.post('/login', { email, password });
export const register = (data) => axios.post('/register', data);
export const getUser = () => axios.get('/user');
