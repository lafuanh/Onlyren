// @/api/auth.js
// Import the pre-configured apiClient instance
import apiClient from './client' // Make sure the path is correct: ./client or ../client if in a nested folder

/**
 * Login user
 */
export const login = async (credentials) => {
  try {
    // Use apiClient for the request
    const response = await apiClient.post('/login', credentials)

    if (response.data.success) {
      const { user, token } = response.data.data

      // Store token and user data. The apiClient's interceptor will also pick this up.
      localStorage.setItem('auth_token', token)
      localStorage.setItem('user', JSON.stringify(user))

      return { user, token }
    }

    throw new Error(response.data.message || 'Login failed')
  } catch (error) {
    // Error handling largely remains the same, but the apiClient interceptors
    // will handle global error concerns (like 401 redirects).
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
    // Use apiClient for the request
    const response = await apiClient.post('/register', {
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
    // The apiClient interceptor will add the token if it exists.
    // We only need to check if a token is in localStorage *before* making the request
    // if we want to immediately return null without an API call.
    const token = localStorage.getItem('auth_token')
    if (!token) {
      // No token, no need to make an API call
      return null
    }

    // Use apiClient for the request
    const response = await apiClient.get('/user')

    if (response.data.success) {
      const user = response.data.data
      localStorage.setItem('user', JSON.stringify(user))
      return user
    }

    return null
  } catch (error) {
    // The 401 interceptor in client.js will handle clearing tokens and redirecting.
    // For other errors, we might still want to clear, but often the 401 handler is sufficient.
    console.error('Error fetching current user:', error);
    // You might still want to clear local storage here if the API call itself fails
    // for non-401 reasons that indicate a bad session.
    // For now, let's rely on the apiClient's 401 handler for token clearing.
    return null;
  }
}

/**
 * Logout user
 */
export const logout = async () => {
  try {
    // Use apiClient for the request
    await apiClient.post('/logout')
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    // Ensure tokens are cleared regardless of API response for logout
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
    // If you have a separate refresh token for logout specifically, clear it too
    localStorage.removeItem('refresh_token');
    sessionStorage.removeItem('refresh_token'); // Clear from both just in case
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