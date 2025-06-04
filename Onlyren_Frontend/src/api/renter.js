// @/api/renter.js
import axios from 'axios'

// Set up axios defaults
////process.env.VUE_APP_API_URL ||
const API_BASE_URL = 'http://localhost:8080/api'
axios.defaults.baseURL = API_BASE_URL

// Add request interceptor to include auth token
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Add response interceptor to handle token expiration
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

/**
 * Fetch renter profile
 */
export const fetchRenterProfile = async () => {
  try {
    const response = await axios.get('/renter/profile')
    
    if (response.data.success) {
      return response.data.data
    }
    
    throw new Error(response.data.message || 'Failed to fetch profile')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to fetch profile')
  }
}

/**
 * Update renter profile
 */
export const updateRenterProfile = async (profileData) => {
  try {
    const response = await axios.put('/renter/profile', profileData)
    
    if (response.data.success) {
      return response.data.data
    }
    
    throw new Error(response.data.message || 'Failed to update profile')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      throw new Error(errors.join(', '))
    }
    throw new Error('Failed to update profile')
  }
}

/**
 * Fetch renter's rooms
 */
export const fetchRenterRooms = async () => {
  try {
    const response = await axios.get('/renter/rooms')
    
    if (response.data.success) {
      return response.data.data
    }
    
    throw new Error(response.data.message || 'Failed to fetch rooms')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to fetch rooms')
  }
}

/**
 * Create new room
 */
export const createRoom = async (roomData) => {
  try {
    const response = await axios.post('/renter/rooms', roomData)
    
    if (response.data.success) {
      return response.data.data
    }
    
    throw new Error(response.data.message || 'Failed to create room')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      throw new Error(errors.join(', '))
    }
    throw new Error('Failed to create room')
  }
}

/**
 * Get specific room
 */
export const fetchRenterRoom = async (roomId) => {
  try {
    const response = await axios.get(`/renter/rooms/${roomId}`)
    
    if (response.data.success) {
      return response.data.data
    }
    
    throw new Error(response.data.message || 'Failed to fetch room')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to fetch room')
  }
}

/**
 * Update room
 */
export const updateRoom = async (roomId, roomData) => {
  try {
    const response = await axios.put(`/renter/rooms/${roomId}`, roomData)
    
    if (response.data.success) {
      return response.data.data || { success: true }
    }
    
    throw new Error(response.data.message || 'Failed to update room')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      throw new Error(errors.join(', '))
    }
    throw new Error('Failed to update room')
  }
}

/**
 * Delete room
 */
export const deleteRoom = async (roomId) => {
  try {
    const response = await axios.delete(`/renter/rooms/${roomId}`)
    
    if (response.data.success) {
      return { success: true }
    }
    
    throw new Error(response.data.message || 'Failed to delete room')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to delete room')
  }
}

/**
 * Fetch renter's orders/bookings
 */
export const fetchRenterOrders = async () => {
  try {
    const response = await axios.get('/renter/orders')
    
    if (response.data.success) {
      return response.data.data
    }
    
    throw new Error(response.data.message || 'Failed to fetch orders')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to fetch orders')
  }
}

/**
 * Approve order
 */
export const approveOrder = async (orderId) => {
  try {
    const response = await axios.put(`/renter/orders/${orderId}/approve`)
    
    if (response.data.success) {
      return { success: true }
    }
    
    throw new Error(response.data.message || 'Failed to approve order')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to approve order')
  }
}

/**
 * Reject order
 */
export const rejectOrder = async (orderId, reason = '') => {
  try {
    const response = await axios.put(`/renter/orders/${orderId}/reject`, {
      reason: reason
    })
    
    if (response.data.success) {
      return { success: true }
    }
    
    throw new Error(response.data.message || 'Failed to reject order')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to reject order')
  }
}

/**
 * Complete order
 */
export const completeOrder = async (orderId) => {
  try {
    const response = await axios.put(`/renter/orders/${orderId}/complete`)
    
    if (response.data.success) {
      return { success: true }
    }
    
    throw new Error(response.data.message || 'Failed to complete order')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to complete order')
  }
}

/**
 * Fetch renter's conversations
 */
export const fetchRenterConversations = async () => {
  try {
    const response = await axios.get('/renter/conversations')
    
    if (response.data.success) {
      return response.data.data
    }
    
    throw new Error(response.data.message || 'Failed to fetch conversations')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to fetch conversations')
  }
}

/**
 * Send message
 */
export const sendMessage = async (messageData) => {
  try {
    const response = await axios.post('/renter/messages', messageData)
    
    if (response.data.success) {
      return { success: true }
    }
    
    throw new Error(response.data.message || 'Failed to send message')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      throw new Error(errors.join(', '))
    }
    throw new Error('Failed to send message')
  }
}

/**
 * Logout function for renter
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