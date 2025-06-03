// src/api/payment.js
import axios from 'axios'

// Replace with your backend URL
const API_URL = 'http://your-backend-api-url/api' 

// Fetch reservation details by reservation ID
export const fetchReservationDetails = async (reservationId) => {
  try {
    const response = await axios.get(`${API_URL}/reservations/${reservationId}`)
    return response.data
  } catch (err) {
    console.error('Error fetching reservation details:', err)
    throw new Error('Unable to fetch reservation details.')
  }
}

// Process payment
export const processPayment = async (paymentData) => {
  try {
    const response = await axios.post(`${API_URL}/payments/process`, paymentData)
    return response.data
  } catch (err) {
    console.error('Error processing payment:', err)
    throw new Error('Payment processing failed. Please try again later.')
  }
}
