// src/api/payment.js
import apiClient from './client'; // Use the central apiClient

/**
 * Fetch detailed information for a specific reservation to be paid
 * @param {string|number} reservationId - The reservation ID
 * @returns {Promise} API response with reservation and payment details
 */
export const fetchReservationForPayment = async (reservationId) => {
    if (!reservationId) {
        throw new Error('Reservation ID is required.');
    }
    try {
        const response = await apiClient.get(`/reservations/${reservationId}`);
        if (response.data && response.data.success) {
            return response.data.data;
        }
        throw new Error(response.data.message || 'Could not fetch reservation details.');
    } catch (error) {
        console.error('Error fetching reservation for payment:', error);
        throw new Error(error.response?.data?.message || error.message);
    }
};


/**
 * Process the payment for a given reservation
 * @param {string|number} reservationId - The reservation ID
 * @param {object} paymentData - Payment details (method, notes, etc.)
 * @returns {Promise} API response with the confirmed payment details
 */
export const processPayment = async (reservationId, paymentData) => {
  if (!reservationId || !paymentData) {
    throw new Error('Reservation ID and payment data are required.');
  }
  try {
    // This route should point to your PaymentController's processPayment method
    const response = await apiClient.post(`/payments/reservation/${reservationId}`, paymentData);
    if (response.data && response.data.success) {
      return response.data.data;
    }
    throw new Error(response.data.message || 'Payment processing failed.');
  } catch (error) {
    console.error('Error processing payment:', error);
    throw new Error(error.response?.data?.message || error.message);
  }
};