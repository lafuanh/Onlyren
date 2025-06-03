import axios from 'axios'

// Assuming you have defined your API URL in the `.env` file
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/api' // Default to local if not defined

// Fetch room details by room ID
export const fetchRoomDetails = async (roomId) => {
  try {
    const response = await axios.get(`${API_URL}/rooms/${roomId}`)
    return response.data
  } catch (error) {
    console.error('Error fetching room details:', error)
    throw new Error('Unable to fetch room details.')
  }
}

// Create a new reservation for a room
export const createReservation = async (reservationData) => {
  try {
    const response = await axios.post(`${API_URL}/reservations`, reservationData)
    return response.data
  } catch (error) {
    console.error('Error creating reservation:', error)
    throw new Error('Reservation creation failed.')
  }
}

// Confirm a reservation after creation
export const confirmReservation = async (reservationId) => {
  try {
    const response = await axios.patch(`${API_URL}/reservations/${reservationId}/confirm`)
    return response.data
  } catch (error) {
    console.error('Error confirming reservation:', error)
    throw new Error('Reservation confirmation failed.')
  }
}

// Process the payment for a reservation
export const processPayment = async (paymentData) => {
  try {
    const response = await axios.post(`${API_URL}/payments/process`, paymentData)
    return response.data
  } catch (error) {
    console.error('Error processing payment:', error)
    throw new Error('Payment processing failed.')
  }
}

// Fetch reviews for a specific room
export const fetchReviews = async (roomId) => {
  try {
    const response = await axios.get(`${API_URL}/rooms/${roomId}/reviews`)
    return response.data
  } catch (error) {
    console.error('Error fetching reviews:', error)
    throw new Error('Failed to fetch reviews.')
  }
}

// Fetch all rooms (optional - could be used for listing rooms)
export const fetchRooms = async (params) => {
  try {
    const response = await axios.get(`${API_URL}/rooms`, { params })
    return response.data
  } catch (error) {
    console.error('Error fetching rooms:', error)
    throw new Error('Failed to fetch rooms.')
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
    throw new Error('Failed to create room.')
  }
}

// Update room status (active or inactive)
export const updateRoomStatus = async (roomId, status) => {
  try {
    const response = await axios.patch(`${API_URL}/rooms/${roomId}/status`, { status })
    return response.data
  } catch (error) {
    console.error('Error updating room status:', error)
    throw new Error('Failed to update room status.')
  }
}

// Fetch all rooms belonging to a renter
export const fetchRenterRooms = async () => {
  try {
    const response = await axios.get(`${API_URL}/renter/rooms`)
    return response.data
  } catch (error) {
    console.error('Error fetching renter rooms:', error)
    throw new Error('Failed to fetch renter rooms.')
  }
}

// Delete a room
export const deleteRoom = async (id) => {
  try {
    const response = await axios.delete(`${API_URL}/rooms/${id}`)
    return response.data
  } catch (error) {
    console.error('Error deleting room:', error)
    throw new Error('Failed to delete room.')
  }
}

// Edit a room's details
export const editRoom = async (roomData) => {
  try {
    const response = await axios.put(`${API_URL}/rooms/${roomData.id}`, roomData)
    return response.data
  } catch (error) {
    console.error('Error editing room:', error)
    throw new Error('Failed to edit room.')
  }
}
