import axios from 'axios';

const API_URL = 'http://127.0.0.1:8080/api'; // Laravel backend API URL

// Login API
export const login = async (credentials) => {
    try {
        const response = await axios.post(`${API_URL}/login`, credentials);
        // Save token to localStorage
        localStorage.setItem('token', response.data.token);
        return response.data;
    } catch (error) {
        console.error('Login failed:', error);
        throw error;
    }
};

// Register API
export const register = async (userData) => {
    try {
        const response = await axios.post(`${API_URL}/register`, userData);
        localStorage.setItem('token', response.data.token);
        return response.data;
    } catch (error) {
        console.error('Registration failed:', error);
        throw error;
    }
};

// Logout API
export const logout = async () => {
    try {
        await axios.post(`${API_URL}/logout`, null, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('token')}`, // Use the token
            },
        });
        localStorage.removeItem('token');
    } catch (error) {
        console.error('Logout failed:', error);
    }
};

// Get current authenticated user
export const getCurrentUser = async () => {
    try {
        const response = await axios.get(`${API_URL}/user`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('token')}`, // Add token to header
            },
        });
        return response.data;
    } catch (error) {
        console.error('Error fetching current user:', error);
        throw error;
    }
};

// Axios interceptor to add token to every request
export const setupAxiosInterceptors = () => {
    axios.interceptors.request.use(
        (config) => {
            const token = localStorage.getItem('token');
            if (token) {
                config.headers['Authorization'] = `Bearer ${token}`;
            }
            return config;
        },
        (error) => {
            return Promise.reject(error);
        }
    );

    // Handle response errors
    axios.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error.response && error.response.status === 401) {
                localStorage.removeItem('token'); // Clear the token if invalid
                window.location.href = '/login'; // Redirect to login
            }
            return Promise.reject(error);
        }
    );
};
