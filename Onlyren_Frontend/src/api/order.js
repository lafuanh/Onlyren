import apiClient from './client'

export const fetchOrders = async () => {
  const response = await apiClient.get('/api/orders')
  return response.data
}

export const manageOrder = async (orderId) => {
  const response = await apiClient.put(`/api/orders/${orderId}/manage`)
  return response.data
}
