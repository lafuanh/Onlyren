// src/api/reservation.js
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL

export const fetchUserReservations = async () => {
  try {
    const response = await axios.get(`${API_URL}/user/reservations`)
    return response.data
  } catch (error) {
    console.error('Error fetching user reservations:', error)
    throw error
  }
}

export const fetchRenterReservations = async () => {
  try {
    const response = await axios.get(`${API_URL}/renter/reservations`)
    return response.data
  } catch (error) {
    console.error('Error fetching renter reservations:', error)
    throw error
  }
}

export const createReservation = async (reservationData) => {
  try {
    const response = await axios.post(`${API_URL}/reservations`, reservationData)
    return response.data
  } catch (error) {
    console.error('Error creating reservation:', error)
    throw error
  }
}

export const updateReservationStatus = async (reservationId, newStatus) => {
  try {
    const response = await axios.patch(`${API_URL}/reservations/${reservationId}/status`, {
      status: newStatus
    })
    return response.data
  } catch (error) {
    console.error('Error updating reservation status:', error)
    throw error
  }
}