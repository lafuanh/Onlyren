import axios from 'axios'

export const fetchUserProfile = async () => {
  const response = await axios.get('/api/user/profile')
  return response.data
}

export const fetchUserReservations = async () => {
  const response = await axios.get('/api/user/reservations')
  return response.data
}

export const fetchUserPayments = async () => {
  const response = await axios.get('/api/user/payments')
  return response.data
}

export const fetchUserConversations = async () => {
  const response = await axios.get('/api/user/conversations')
  return response.data
}

export const logout = async () => {
  const response = await axios.post('/api/auth/logout')
  return response.data
}
