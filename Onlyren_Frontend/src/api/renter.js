// src/api/renter.js
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL

export const fetchRenterProfile = async () => {
  try {
    const response = await axios.get(`${API_URL}/renter/profile`)
    return response.data
  } catch (error) {
    console.error('Error fetching renter profile:', error)
    throw error
  }
}

export const updateRenterProfile = async (profileData) => {
  try {
    const response = await axios.post(`${API_URL}/renter/profile`, profileData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  } catch (error) {
    console.error('Error updating renter profile:', error)
    throw error
  }
}