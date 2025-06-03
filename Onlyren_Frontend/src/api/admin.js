// src/api/admin.js
import axios from 'axios'

const API_URL = 'http://your-backend-api-url/api/admin'  // Update with your API base URL

// Admin Profile API
export const fetchAdminProfile = async () => {
  const response = await axios.get(`${API_URL}/profile`)
  return response.data
}

// Dashboard Stats API
export const fetchDashboardStats = async () => {
  const response = await axios.get(`${API_URL}/dashboard/stats`)
  return response.data
}

// Users Management API
export const fetchAllUsers = async () => {
  const response = await axios.get(`${API_URL}/users`)
  return response.data
}

export const addUser = async (userData) => {
  const response = await axios.post(`${API_URL}/users`, userData)
  return response.data
}

export const updateUser = async (userData) => {
  const response = await axios.put(`${API_URL}/users/${userData.id}`, userData)
  return response.data
}

export const deleteUser = async (userId) => {
  const response = await axios.delete(`${API_URL}/users/${userId}`)
  return response.data
}

export const activateUser = async (userId) => {
  const response = await axios.patch(`${API_URL}/users/${userId}/activate`)
  return response.data
}

export const deactivateUser = async (userId) => {
  const response = await axios.patch(`${API_URL}/users/${userId}/deactivate`)
  return response.data
}

// Properties Management API
export const fetchAllProperties = async () => {
  const response = await axios.get(`${API_URL}/properties`)
  return response.data
}

export const approveProperty = async (propertyId) => {
  const response = await axios.patch(`${API_URL}/properties/${propertyId}/approve`)
  return response.data
}

export const rejectProperty = async (propertyId) => {
  const response = await axios.patch(`${API_URL}/properties/${propertyId}/reject`)
  return response.data
}

export const deleteProperty = async (propertyId) => {
  const response = await axios.delete(`${API_URL}/properties/${propertyId}`)
  return response.data
}

// Orders Management API
export const fetchAllOrders = async () => {
  const response = await axios.get(`${API_URL}/orders`)
  return response.data
}

export const updateOrderStatus = async (orderId, status) => {
  const response = await axios.put(`${API_URL}/orders/${orderId}/status`, { status })
  return response.data
}

// Payments Management API
export const fetchAllPayments = async () => {
  const response = await axios.get(`${API_URL}/payments`)
  return response.data
}

export const verifyPayment = async (paymentId) => {
  const response = await axios.patch(`${API_URL}/payments/${paymentId}/verify`)
  return response.data
}

export const rejectPayment = async (paymentId) => {
  const response = await axios.patch(`${API_URL}/payments/${paymentId}/reject`)
  return response.data
}

// Reports API
export const fetchReports = async () => {
  const response = await axios.get(`${API_URL}/reports`)
  return response.data
}

export const generateReport = async (reportType) => {
  const response = await axios.post(`${API_URL}/reports/generate`, { reportType })
  return response.data
}

export const exportReport = async (reportId) => {
  const response = await axios.get(`${API_URL}/reports/${reportId}/export`)
  return response.data
}

// Settings API
export const fetchSettings = async () => {
  const response = await axios.get(`${API_URL}/settings`)
  return response.data
}

export const updateSettings = async (settingsData) => {
  const response = await axios.put(`${API_URL}/settings`, settingsData)
  return response.data
}

// Logout API
export const logout = async () => {
  const response = await axios.post(`${API_URL}/logout`)
  return response.data
}
