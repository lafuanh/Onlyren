import axios from './axios';

export const getRooms = () => axios.get('/rooms');
export const getRoomDetail = (id) => axios.get(`/rooms/${id}`);
export const createRoom = (data) => axios.post('/rooms', data);
