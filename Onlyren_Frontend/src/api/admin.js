// src/api/admin.js
import apiClient from './client'

// Admin Profile API
export const fetchAdminProfile = async () => {
  try {
    const response = await apiClient.get('/user')
    return response.data.data
  } catch (error) {
    console.error('Error fetching admin profile:', error)
    throw error
  }
}

// Dashboard Stats API
export const fetchDashboardStats = async () => {
  try {
    const response = await apiClient.get('/admin/dashboard')
    return response.data.data
  } catch (error) {
    console.error('Error fetching dashboard stats:', error)
    throw error
  }
}

// Users Management API
export const fetchAllUsers = async (params = {}) => {
  try {
    const response = await apiClient.get('/admin/users', { params })
    return response.data
  } catch (error) {
    console.error('Error fetching users:', error)
    throw error
  }
}

export const getUser = async (userId) => {
  try {
    const response = await apiClient.get(`/admin/users/${userId}`)
    return response.data.data
  } catch (error) {
    console.error('Error fetching user:', error)
    throw error
  }
}

export const updateUser = async (userId, userData) => {
  try {
    const response = await apiClient.put(`/admin/users/${userId}`, userData)
    return response.data
  } catch (error) {
    console.error('Error updating user:', error)
    throw error
  }
}

export const deleteUser = async (userId) => {
  try {
    const response = await apiClient.delete(`/admin/users/${userId}`)
    return response.data
  } catch (error) {
    console.error('Error deleting user:', error)
    throw error
  }
}

// Rooms/Properties Management API
export const fetchAllProperties = async (params = {}) => {
  try {
    const response = await apiClient.get('/admin/rooms', { params })
    return response.data
  } catch (error) {
    console.error('Error fetching properties:', error)
    throw error
  }
}

export const getProperty = async (propertyId) => {
  try {
    const response = await apiClient.get(`/admin/rooms/${propertyId}`)
    return response.data.data
  } catch (error) {
    console.error('Error fetching property:', error)
    throw error
  }
}

export const updateProperty = async (propertyId, propertyData) => {
  try {
    const response = await apiClient.put(`/admin/rooms/${propertyId}`, propertyData)
    return response.data
  } catch (error) {
    console.error('Error updating property:', error)
    throw error
  }
}

export const deleteProperty = async (propertyId) => {
  try {
    const response = await apiClient.delete(`/admin/rooms/${propertyId}`)
    return response.data
  } catch (error) {
    console.error('Error deleting property:', error)
    throw error
  }
}

// Orders/Reservations Management API
export const fetchAllOrders = async (params = {}) => {
  try {
    const response = await apiClient.get('/admin/reservations', { params })
    return response.data
  } catch (error) {
    console.error('Error fetching orders:', error)
    throw error
  }
}

export const getOrder = async (orderId) => {
  try {
    const response = await apiClient.get(`/admin/reservations/${orderId}`)
    return response.data.data
  } catch (error) {
    console.error('Error fetching order:', error)
    throw error
  }
}

export const approveReservation = async (reservationId) => {
  try {
    const response = await apiClient.put(`/admin/reservations/${reservationId}/approve`)
    return response.data
  } catch (error) {
    console.error('Error approving reservation:', error)
    throw error
  }
}

export const rejectReservation = async (reservationId) => {
  try {
    const response = await apiClient.put(`/admin/reservations/${reservationId}/reject`)
    return response.data
  } catch (error) {
    console.error('Error rejecting reservation:', error)
    throw error
  }
}

export const completeReservation = async (reservationId) => {
  try {
    const response = await apiClient.put(`/admin/reservations/${reservationId}/complete`)
    return response.data
  } catch (error) {
    console.error('Error completing reservation:', error)
    throw error
  }
}

export const deleteReservation = async (reservationId) => {
  try {
    const response = await apiClient.delete(`/admin/reservations/${reservationId}`)
    return response.data
  } catch (error) {
    console.error('Error deleting reservation:', error)
    throw error
  }
}

// Payments Management API
export const fetchAllPayments = async (params = {}) => {
  try {
    const response = await apiClient.get('/admin/payments', { params })
    return response.data
  } catch (error) {
    console.error('Error fetching payments:', error)
    throw error
  }
}

export const getPayment = async (paymentId) => {
  try {
    const response = await apiClient.get(`/admin/payments/${paymentId}`)
    return response.data.data
  } catch (error) {
    console.error('Error fetching payment:', error)
    throw error
  }
}

export const confirmPayment = async (paymentId) => {
  try {
    const response = await apiClient.put(`/admin/payments/${paymentId}/confirm`)
    return response.data
  } catch (error) {
    console.error('Error confirming payment:', error)
    throw error
  }
}

export const deletePayment = async (paymentId) => {
  try {
    const response = await apiClient.delete(`/admin/payments/${paymentId}`)
    return response.data
  } catch (error) {
    console.error('Error deleting payment:', error)
    throw error
  }
}

// Reports API
export const fetchReports = async () => {
  try {
    const [reservationReport, paymentReport] = await Promise.all([
      apiClient.get('/admin/reports/reservations'),
      apiClient.get('/admin/reports/payments')
    ])
    
    return {
      reservations: reservationReport.data.data,
      payments: paymentReport.data.data
    }
  } catch (error) {
    console.error('Error fetching reports:', error)
    throw error
  }
}

export const generateReservationReport = async (params = {}) => {
  try {
    const response = await apiClient.get('/admin/reports/reservations', { params })
    return response.data.data
  } catch (error) {
    console.error('Error generating reservation report:', error)
    throw error
  }
}

export const generatePaymentReport = async (params = {}) => {
  try {
    const response = await apiClient.get('/admin/reports/payments', { params })
    return response.data.data
  } catch (error) {
    console.error('Error generating payment report:', error)
    throw error
  }
}

// Settings API (placeholder - you can implement this later)
export const fetchSettings = async () => {
  try {
    // For now, return default settings
    return {
      site_name: 'OnlyRent',
      contact_email: 'admin@onlyrent.com',
      maintenance_mode: false
    }
  } catch (error) {
    console.error('Error fetching settings:', error)
    throw error
  }
}

export const updateSettings = async (settingsData) => {
  try {
    // For now, just return success
    return { success: true, message: 'Settings updated successfully' }
  } catch (error) {
    console.error('Error updating settings:', error)
    throw error
  }
}

// Logout API
export const logout = async () => {
  try {
    const response = await apiClient.post('/logout')
    return response.data
  } catch (error) {
    console.error('Error logging out:', error)
    throw error
  }
}
