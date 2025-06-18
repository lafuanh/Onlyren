import apiClient from './client'

export const fetchUserProfile = async () => {
  try {
    const response = await apiClient.get('/user/profile')
    return response.data
  } catch (error) {
    console.error('Error fetching user profile:', error)
    throw error
  }
}

export const fetchUserReservations = async () => {
  try {
    const response = await apiClient.get('/user/reservations')
    return response.data
  } catch (error) {
    console.error('Error fetching user reservations:', error)
    throw error
  }
}

export const fetchUserPayments = async () => {
  try {
    const response = await apiClient.get('/user/payments')
    return response.data
  } catch (error) {
    console.error('Error fetching user payments:', error)
    throw error
  }
}

export const fetchUserConversations = async () => {
  try {
    const response = await apiClient.get('/messages/conversations')
    return response.data
  } catch (error) {
    console.error('Error fetching user conversations:', error)
    throw error
  }
}

export const logout = async () => {
  try {
    const response = await apiClient.post('/logout')
    return response.data
  } catch (error) {
    console.error('Error logging out:', error)
    throw error
  }
}
