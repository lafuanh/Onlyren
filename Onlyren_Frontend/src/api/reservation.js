// @/api/reservation.js
import apiClient from './client' // Import the pre-configured apiClient instance

export const fetchUserReservations = async () => {
  try {
    const response = await apiClient.get('/user/reservations') // Use apiClient. No need for `${API_URL}`
    return response.data
  } catch (error) {
    console.error('Error fetching user reservations:', error)
    // The apiClient interceptors will handle 401s, etc.
    // You can keep specific error handling here if needed,
    // otherwise, just re-throw or handle as per your app's needs.
    throw error
  }
}

export const fetchRenterReservations = async () => {
  try {
    const response = await apiClient.get('/renter/reservations') // Use apiClient
    return response.data
  } catch (error) {
    console.error('Error fetching renter reservations:', error)
    throw error
  }
}

/**
 * Create a new reservation
 * @param {Object} reservationData - Reservation details
 * @returns {Promise} API response with reservation data
 */
export const createReservation = async (reservationData) => {
    try {
        // --- Validation for missing fields ---
        const requiredFields = ['room_id', 'start_date', 'end_date', 'start_time', 'end_time', 'guests'];

        const missingFields = [];
        for (const field of requiredFields) {
            if (!reservationData[field]) {
                missingFields.push(field.replace('_', ' '));
            }
        }

        if (missingFields.length > 0) {
            throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
        }

        // Send the reservation request
        const response = await apiClient.post('/reservations', reservationData, { timeout: 15000 });

        // Return the full response, including 'data'
        return response.data;
    } catch (error) {
        console.error('Error creating reservation:', error);

        // Handle validation errors from the backend
        if (error.response?.status === 422) {
            const validationErrors = error.response.data.errors;
            if (validationErrors) {
                const errorMessages = Object.values(validationErrors).flat();
                throw new Error(errorMessages.join(', '));
            }
        }

        // Handle conflicts (e.g., room availability)
        if (error.response?.status === 409) {
            throw new Error('Time slot is no longer available.');
        }

        // General error message
        throw new Error(error.response?.data?.message || error.message || 'Failed to create reservation.');
    }
};

export const updateReservationStatus = async (reservationId, newStatus) => {
  try {
    const response = await apiClient.patch(`/reservations/${reservationId}/status`, {
      status: newStatus
    }) // Use apiClient
    return response.data
  } catch (error) {
    console.error('Error updating reservation status:', error)
    throw error
  }
}

export const cancelUserReservation = async (reservationId) => { // Removed newStatus as it's not used in a typical cancel API call
  try {
    // Assuming cancellation is a PATCH/PUT to update status or a DELETE
    // If it's a PATCH to update status, you might pass 'cancelled'
    const response = await apiClient.patch(`/reservations/${reservationId}/cancel`);
    // Or if it's a dedicated endpoint for cancellation:
    // const response = await apiClient.post(`/reservations/${reservationId}/cancel`);
    // Or if it's a DELETE operation:
    // const response = await apiClient.delete(`/reservations/${reservationId}`);
    return response.data;
  } catch (error) {
    console.error('Error cancelling user reservation:', error);
    throw error;
  }
}