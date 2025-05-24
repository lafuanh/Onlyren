// src/api/auth.js
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL

// Mock function to simulate an API response for authenticated user
export const mockGetCurrentUser = () => {
  return new Promise((resolve, reject) => {
    // Simulate a successful API response
    setTimeout(() => {
      const token = localStorage.getItem('token')
      if (token) {
        resolve({ id: 1, name: 'John Doe', email: 'john.doe@example.com' }) // Mock user data
      } else {
        reject('No authenticated user') // Reject if no token
      }
    }, 1000)
  })
}

// Get current authenticated user (mock version)
export const getCurrentUser = async () => {
  return mockGetCurrentUser()
}

// Mock Login and Register methods for testing
export const login = async (credentials) => {
  const token = 'mockedToken' // Mocked token
  localStorage.setItem('token', token)
  return { token }
}

export const register = async (userData) => {
  const token = 'mockedToken' // Mocked token
  localStorage.setItem('token', token)
  return { token }
}

export const logout = async () => {
  localStorage.removeItem('token')
}







// Interceptor setup for Axios to attach the token to every request
export const setupAxiosInterceptors = () => {
  axios.interceptors.request.use(
    (config) => {
      const token = localStorage.getItem('token')
      if (token) {
        config.headers['Authorization'] = `Bearer ${token}`
      }
      return config
    },
    (error) => {
      return Promise.reject(error)
    }
  )

  // Response interceptor to handle authentication errors
  axios.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response && error.response.status === 401) {
        localStorage.removeItem('token') // Clear the token if it's invalid
        window.location.href = '/login' // Redirect to the login page
      }
      return Promise.reject(error)
    }
  )
}