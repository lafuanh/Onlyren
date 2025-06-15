// @/api/renter.js
import apiClient from './client' // Import the pre-configured apiClient instance

/**
 * Fetch renter profile
 */
export const fetchRenterProfile = async () => {
  try {
    const response = await apiClient.get('/renter/profile') // Use apiClient

    if (response.data.success !== false) {
      return response.data.data || response.data
    }

    throw new Error(response.data.message || 'Failed to fetch profile')
  } catch (error) {
    // Error handling: apiClient's interceptors will handle 401s and other global errors.
    // Specific error messages are still handled here.
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
    const response = await apiClient.put('/renter/profile', profileData) // Use apiClient

    if (response.data.success !== false) {
      return response.data.data || response.data
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
 * Fetch renter dashboard data
 */
export const fetchRenterDashboard = async () => {
  try {
    const response = await apiClient.get('/renter/dashboard') // Use apiClient

    if (response.data.success !== false) {
      return response.data.data || response.data
    }

    throw new Error(response.data.message || 'Failed to fetch dashboard data')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to fetch dashboard data')
  }
}

/**
 * Fetch renter's rooms
 */
export const fetchRenterRooms = async () => {
  try {
    const response = await apiClient.get('/renter/rooms') // Use apiClient

    if (response.data.success !== false) {
      return response.data.data || response.data
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
    // If roomData includes files (e.g., images), ensure it's FormData.
    // apiClient's interceptor already handles FormData content-type.
    const response = await apiClient.post('/renter/rooms', roomData) // Use apiClient

    if (response.data.success !== false) {
      return response.data.data || response.data
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
    const response = await apiClient.get(`/renter/rooms/${roomId}`) // Use apiClient

    if (response.data.success !== false) {
      return response.data.data || response.data
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
export const updateRoom = async (roomId, roomData, hasFile = false) => {
  try {
    let response;
    
    if (hasFile) {
      // Use POST with FormData and _method PUT for file uploads
      response = await apiClient.post(`/renter/rooms/${roomId}`, roomData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        }
      });
    } else {
      // Use PUT with JSON for regular updates
      response = await apiClient.put(`/renter/rooms/${roomId}`, roomData, {
        headers: {
          'Content-Type': 'application/json',
        }
      });
    }

    if (response.data.success !== false) {
      return response.data.data || response.data || { success: true }
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
    const response = await apiClient.delete(`/renter/rooms/${roomId}`) // Use apiClient

    if (response.data.success !== false) {
      return { success: true }
    }

    throw new Error(response.data.message || 'Failed to delete room')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    // This is the line with the error:
    // It should be: throw new Error('Failed to delete room')
    throw new Error('Failed to delete room') // <--- Corrected line
  }
}

/**
 * Fetch renter's reservations (Updated from orders to reservations)
 */
export const fetchRenterReservations = async () => {
  try {
    const response = await apiClient.get('/renter/reservations') // Use apiClient

    if (response.data.success !== false) {
      return response.data.data || response.data
    }

    throw new Error(response.data.message || 'Failed to fetch reservations')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to fetch reservations')
  }
}

/**
 * Approve reservation (Updated from order to reservation)
 */
export const approveReservation = async (reservationId) => {
  try {
    const response = await apiClient.put(`/renter/reservations/${reservationId}/approve`) // Use apiClient

    if (response.data.success !== false) {
      return { success: true }
    }

    throw new Error(response.data.message || 'Failed to approve reservation')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to approve reservation')
  }
}

/**
 * Reject reservation (Updated from order to reservation)
 */
export const rejectReservation = async (reservationId, reason = '') => {
  try {
    const response = await apiClient.put(`/renter/reservations/${reservationId}/reject`, {
      reason: reason
    }) // Use apiClient

    if (response.data.success !== false) {
      return { success: true }
    }

    throw new Error(response.data.message || 'Failed to reject reservation')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to reject reservation')
  }
}

/**
 * Complete reservation (Updated from order to reservation)
 */
export const completeReservation = async (reservationId) => {
  try {
    const response = await apiClient.put(`/renter/reservations/${reservationId}/complete`) // Use apiClient

    if (response.data.success !== false) {
      return { success: true }
    }

    throw new Error(response.data.message || 'Failed to complete reservation')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to complete reservation')
  }
}

/**
 * Fetch renter's payments
 */
export const fetchRenterPayments = async () => {
  try {
    const response = await apiClient.get('/renter/payments') // Use apiClient

    if (response.data.success !== false) {
      return response.data.data || response.data
    }

    throw new Error(response.data.message || 'Failed to fetch payments')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to fetch payments')
  }
}

/**
 * Confirm payment
 */
export const confirmPayment = async (paymentId) => {
  try {
    const response = await apiClient.put(`/renter/payments/${paymentId}/confirm`) // Use apiClient

    if (response.data.success !== false) {
      return { success: true }
    }

    throw new Error(response.data.message || 'Failed to confirm payment')
  } catch (error) {
    if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    }
    throw new Error('Failed to confirm payment')
  }
}

/**
 * Fetch renter's conversations
 */
export const fetchRenterConversations = async () => {
  try {
    const response = await apiClient.get('/renter/conversations') // Use apiClient

    if (response.data.success !== false) {
      return response.data.data || response.data
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
    const response = await apiClient.post('/renter/messages', messageData) // Use apiClient

    if (response.data.success !== false) {
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
    // Use apiClient for the request
    await apiClient.post('/logout')
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    // Ensure tokens are cleared regardless of API response for logout
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
    localStorage.removeItem('refresh_token');
    sessionStorage.removeItem('refresh_token');
  }
}

// Legacy function aliases for backward compatibility
export const fetchRenterOrders = fetchRenterReservations
export const approveOrder = approveReservation
export const rejectOrder = rejectReservation
export const completeOrder = completeReservation