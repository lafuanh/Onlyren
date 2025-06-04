import axios from 'axios'

// Set up axios defaults
//process.env.VUE_APP_API_URL ||
const API_BASE_URL = 'https://onlyren.noupal.pro/api/' 
axios.defaults.baseURL = API_BASE_URL

export const setupAxiosInterceptors = () => {
  // Add request interceptor to include auth token
  axios.interceptors.request.use(
    (config) => {
      const token = localStorage.getItem('auth_token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    },
    (error) => {
      return Promise.reject(error);
    }
  );

  // Add response interceptor to handle token expiration
  axios.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401) {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
      }
      return Promise.reject(error);
    }
  );
};

/**
 * Login user
 */
export const login = async (credentials) => {
  try {
    const response = await axios.post('/login', credentials)
    
    if (response.data.success) {
      const { user, token } = response.data.data
      
      // Store token and user data
      localStorage.setItem('auth_token', token)
      localStorage.setItem('user', JSON.stringify(user))
      
      return { user, token }
    }
    
    throw new Error(response.data.message || 'Login failed')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw error
  }
}

/**
 * Register user
 */
export const register = async (userData) => {
  try {
    const response = await axios.post('/register', {
      name: userData.name,
      email: userData.email,
      password: userData.password,
      password_confirmation: userData.password_confirmation,
      role: userData.role
    })
    
    if (response.data.success) {
      const { user, token } = response.data.data
      
      // Store token and user data
      localStorage.setItem('auth_token', token)
      localStorage.setItem('user', JSON.stringify(user))
      
      return { user, token }
    }
    
    throw new Error(response.data.message || 'Registration failed')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      throw new Error(errors.join(', '))
    }
    throw error
  }
}

/**
 * Get current user
 */
export const getCurrentUser = async () => {
  try {
    const token = localStorage.getItem('auth_token')
    if (!token) {
      return null
    }

    const response = await axios.get('/user')
    
    if (response.data.success) {
      const user = response.data.data
      localStorage.setItem('user', JSON.stringify(user))
      return user
    }
    
    return null
  } catch (error) {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
    return null
  }
}

/**
 * Logout user
 */
export const logout = async () => {
  try {
    await axios.post('/logout')
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
  }
}

/**
 * Get user from localStorage
 */
export const getStoredUser = () => {
  try {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  } catch (error) {
    return null
  }
}

/**
 * Check if user is authenticated
 */
export const isAuthenticated = () => {
  return !!localStorage.getItem('auth_token')
}