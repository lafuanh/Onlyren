import axios from 'axios'

export const fetchOrders = async () => {
  const response = await axios.get('/api/orders')
  return response.data
}

export const manageOrder = async (orderId) => {
  const response = await axios.put(`/api/orders/${orderId}/manage`)
  return response.data
}
