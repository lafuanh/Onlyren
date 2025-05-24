// src/api/user.js
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL

export const fetchUserProfile = async () => {
  try {
    const response = await axios.get(`${API_URL}/user/profile`)
    return response.data
  } catch (error) {
    console.error('Error fetching user profile:', error)
    throw error
  }
}

export const updateUserProfile = async (profileData) => {
  try {
    const response = await axios.post(`${API_URL}/user/profile`, profileData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  } catch (error) {
    console.error('Error updating user profile:', error)
    throw error
  }
}