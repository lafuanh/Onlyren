import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL // Make sure to set this in your .env file or adjust the URL if needed

// Fetch room details by room ID
export const fetchRoomDetails = async (roomId) => {
  try {
    const response = await axios.get(`${API_URL}/rooms/${roomId}`)
    return response.data // Assuming your API responds with room details
  } catch (error) {
    console.error('Error fetching room details:', error)
    throw error // Propagate error for error handling in the component
  }
}

// Create a new reservation for the room
export const createReservation = async (reservationData) => {
  try {
    const response = await axios.post(`${API_URL}/reservations`, reservationData, {
      headers: {
        'Content-Type': 'application/json', // Set the correct content type
      },
    })
    return response.data // Assuming the API responds with reservation data, like success message or confirmation
  } catch (error) {
    console.error('Error creating reservation:', error)
    throw error // Propagate error for error handling in the component
  }
}

// Fetch reviews for a specific room (if needed in future)
export const fetchReviews = async (roomId) => {
  try {
    const response = await axios.get(`${API_URL}/rooms/${roomId}/reviews`) // Adjust this endpoint if necessary
    return response.data
  } catch (error) {
    console.error('Error fetching reviews:', error)
    throw error
  }
}
export const fetchRooms = async (params) => {
  try {
    const response = await axios.get(`${API_URL}/rooms`, { params })
    return response.data // Assuming the response contains the rooms and pagination info
  } catch (error) {
    console.error('Error fetching rooms:', error)
    throw error
  }
}


// Create a new room
export const createNewRoom = async (roomData) => {
  try {
    const response = await axios.post(`${API_URL}/rooms`, roomData, {
      headers: {
        'Content-Type': 'application/json'
      }
    })
    return response.data
  } catch (error) {
    console.error('Error creating room:', error)
    throw error
  }
}

// Update room status (active or inactive)
export const updateRoomStatus = async (roomId, status) => {
  try {
    const response = await axios.patch(`${API_URL}/rooms/${roomId}/status`, { status })
    return response.data
  } catch (error) {
    console.error('Error updating room status:', error)
    throw error
  }
}

// Fetch all rooms belonging to a renter
export const fetchRenterRooms = async () => {
  try {
    const response = await axios.get(`${API_URL}/renter/rooms`)
    return response.data
  } catch (error) {
    console.error('Error fetching renter rooms:', error)
    throw error
  }
}