// @/api/client.js
import axios from 'axios'

// Determine the base URL based on the environment
const API_BASE_URL = import.meta.env.DEV
  ? 'http://localhost:8080/api/' // Development API URL
  : 'https://onlyren.noupal.pro/api/'; // Production API URL

// Create axios instance with base configuration
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000, // 30 seconds timeout
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true,
  // Add retry configuration
  retryAttempts: 3,
  retryDelay: 1000
})

// ... (rest of your client.js code remains the same)
// Helper function to get auth token safely
const getAuthToken = () => {
  try {
    return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')
  } catch (error) {
    console.warn('Unable to access storage for auth token:', error)
    return null
  }
}

// Helper function to clear auth token safely
const clearAuthToken = () => {
  try {
    localStorage.removeItem('auth_token')
    sessionStorage.removeItem('auth_token')
    localStorage.removeItem('user_data')
    sessionStorage.removeItem('user_data')
  } catch (error) {
    console.warn('Unable to clear storage:', error)
  }
}

// Helper function to check if we're in a browser environment
const isBrowser = () => typeof window !== 'undefined'

// Request interceptor to add auth token and handle requests
apiClient.interceptors.request.use(
  (config) => {
    const token = getAuthToken()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    if (isBrowser()) {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      if (csrfToken) {
        config.headers['X-CSRF-TOKEN'] = csrfToken
      }
    }

    // Ensure proper content type for form data
   if (config.data instanceof FormData) {
      // Option 1: Delete the header. This is the standard and usually works.
      delete config.headers['Content-Type']; 
      config.headers['Content-Type'] = false; 
      // Option 2 (Less common, only if delete doesn't work for some reason):
      // config.headers['Content-Type'] = undefined; // Explicitly set to undefined
    }
    // Log requests in development
    if (import.meta.env.DEV) {
      const logData = {
        method: config.method?.toUpperCase(),
        url: config.url,
        baseURL: config.baseURL,
        params: config.params,
        headers: { ...config.headers }
      }

      // Don't log sensitive data
      if (logData.headers.Authorization) {
        logData.headers.Authorization = 'Bearer [HIDDEN]'
      }

      console.log('🚀 API Request:', logData)
    }

    return config
  },
  (error) => {
    console.error('Request interceptor error:', error)
    return Promise.reject(error)
  }
)

// Retry logic helper
const retryRequest = async (error) => {
  const config = error.config

  // Don't retry if we've exceeded max attempts or it's not a retryable error
  if (!config || config.__retryCount >= (config.retryAttempts || 3)) {
    return Promise.reject(error)
  }

  // Only retry on specific error types
  const retryableErrors = [
    'ECONNABORTED', // Timeout
    'ENOTFOUND',    // DNS issues
    'ECONNREFUSED', // Connection refused
    'ECONNRESET',   // Connection reset
    'ETIMEDOUT'     // Timeout
  ]

  const shouldRetry =
    retryableErrors.includes(error.code) ||
    (error.response?.status >= 500 && error.response?.status <= 599) ||
    !error.response // Network error

  if (!shouldRetry) {
    return Promise.reject(error)
  }

  // Increment retry count
  config.__retryCount = (config.__retryCount || 0) + 1

  // Calculate delay with exponential backoff
  const delay = (config.retryDelay || 1000) * Math.pow(2, config.__retryCount - 1)

  console.log(`Retrying request (${config.__retryCount}/${config.retryAttempts}) after ${delay}ms...`)

  // Wait before retrying
  await new Promise(resolve => setTimeout(resolve, delay))

  // Retry the request
  return apiClient.request(config)
}

// Response interceptor to handle common response scenarios
apiClient.interceptors.response.use(
  (response) => {
    // Log responses in development
    if (import.meta.env.DEV) {
      console.log('✅ API Response:', {
        method: response.config.method?.toUpperCase(),
        url: response.config.url,
        status: response.status,
        data: response.data
      })
    }

    return response
  },
  async (error) => {
    // Try to retry the request first
    if (error.config && !error.config.__isRetry) {
      error.config.__isRetry = true
      try {
        return await retryRequest(error)
      } catch (retryError) {
        // If retry fails, continue with normal error handling
        error = retryError
      }
    }

    // Log errors in development
    if (import.meta.env.DEV) {
      console.error('❌ API Error:', {
        method: error.config?.method?.toUpperCase(),
        url: error.config?.url,
        status: error.response?.status,
        message: error.response?.data?.message || error.message,
        errors: error.response?.data?.errors,
        code: error.code
      })
    }

    // Handle response errors
    if (error.response) {
      const { status, data } = error.response

      switch (status) {
        case 401:
          // Unauthorized - clear auth and redirect to login
          console.warn('Authentication failed - clearing tokens')
          clearAuthToken()

          // Only redirect if we're in browser and not already on login page
          if (isBrowser() && window.location.pathname !== '/login') {
            // Dispatch custom event for auth failure
            window.dispatchEvent(new CustomEvent('auth:logout', {
              detail: { reason: 'unauthorized' }
            }))

            // Redirect after a short delay to allow event handlers to run
            setTimeout(() => {
              window.location.href = '/login?reason=session_expired'
            }, 100)
          }
          break

        case 403:
          // Forbidden
          console.error('Access forbidden:', data?.message)
          if (isBrowser()) {
            window.dispatchEvent(new CustomEvent('auth:forbidden', {
              detail: { message: data?.message }
            }))
          }
          break

        case 404:
          // Not found - usually handled by individual API calls
          console.error('Resource not found:', data?.message)
          break

        case 422:
          // Validation errors - usually handled by forms
          console.error('Validation errors:', data?.errors)
          break

        case 429:
          // Too many requests
          console.error('Rate limit exceeded')
          if (isBrowser()) {
            window.dispatchEvent(new CustomEvent('api:rate_limit', {
              detail: { retryAfter: error.response.headers['retry-after'] }
            }))
          }
          break

        case 500:
        case 502:
        case 503:
        case 504:
          // Server errors
          console.error('Server error:', data?.message || 'Internal server error')
          if (isBrowser()) {
            window.dispatchEvent(new CustomEvent('api:server_error', {
              detail: { status, message: data?.message }
            }))
          }
          break

        default:
          console.error('API Error:', data?.message || `HTTP ${status} error`)
      }
    } else if (error.request) {
      // Network error
      console.error('Network error - no response received:', error.message)
      if (isBrowser()) {
        window.dispatchEvent(new CustomEvent('api:network_error', {
          detail: { message: error.message }
        }))
      }
    } else {
      // Request setup error
      console.error('Request setup error:', error.message)
    }

    return Promise.reject(error)
  }
)

// Helper function to check API health
export const checkApiHealth = async () => {
  try {
    const response = await apiClient.get('/health', { timeout: 5000 })
    return { healthy: true, data: response.data }
  } catch (error) {
    console.error('API health check failed:', error)
    return {
      healthy: false,
      error: error.message,
      status: error.response?.status
    }
  }
}

// Helper function to refresh auth token
export const refreshAuthToken = async () => {
  try {
    const refreshToken = localStorage.getItem('refresh_token') || sessionStorage.getItem('refresh_token')
    if (!refreshToken) {
      throw new Error('No refresh token available')
    }

    const response = await apiClient.post('/auth/refresh', {
      refresh_token: refreshToken
    })

    const { access_token, refresh_token: newRefreshToken } = response.data

    // Store new tokens
    const storage = localStorage.getItem('auth_token') ? localStorage : sessionStorage
    storage.setItem('auth_token', access_token)
    if (newRefreshToken) {
      storage.setItem('refresh_token', newRefreshToken)
    }

    return access_token
  } catch (error) {
    console.error('Failed to refresh token:', error)
    clearAuthToken()
    throw error
  }
}

// Helper function to set auth token
export const setAuthToken = (token, refreshToken = null, rememberMe = false) => {
  try {
    const storage = rememberMe ? localStorage : sessionStorage
    storage.setItem('auth_token', token)
    if (refreshToken) {
      storage.setItem('refresh_token', refreshToken)
    }
  } catch (error) {
    console.error('Failed to store auth token:', error)
  }
}

// Helper function to get current user
export const getCurrentUser = async () => {
  try {
    const response = await apiClient.get('/user/profile')
    return response.data.data || response.data
  } catch (error) {
    console.error('Failed to get current user:', error)
    throw error
  }
}

// Add request/response interceptor to handle token refresh
apiClient.interceptors.response.use(
  response => response,
  async error => {
    const originalRequest = error.config

    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true

      try {
        const newToken = await refreshAuthToken()
        originalRequest.headers.Authorization = `Bearer ${newToken}`
        return apiClient.request(originalRequest)
      } catch (refreshError) {
        // Refresh failed, clear tokens and redirect
        clearAuthToken()
        if (isBrowser() && window.location.pathname !== '/login') {
          window.location.href = '/login?reason=token_expired'
        }
      }
    }

    return Promise.reject(error)
  }
)

export default apiClient