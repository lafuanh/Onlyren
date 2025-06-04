<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

class RoomController extends Controller
{
    // GET /rooms
    public function index()
    {
        return Room::all();
    }

    // GET /rooms/{id}
    public function show($id)
    {
        return Room::findOrFail($id);
    }

    // POST /rooms
    public function store(Request $request)
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:100',
            'kapasitas' => 'required|integer',
            'fasilitas' => 'nullable|string',
            'status' => 'required|in:Tersedia,Penuh',
            'harga' => 'required|integer',
        ]);

        $room = Room::create($request->all());
        return response()->json($room, 201);
    }

    // PUT /rooms/{id}
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'nama_ruangan' => 'sometimes|string|max:100',
            'kapasitas' => 'sometimes|integer',
            'fasilitas' => 'nullable|string',
            'status' => 'in:Tersedia,Penuh',
            'harga' => 'sometimes|integer',
        ]);

        $room->update($request->all());
        return response()->json($room);
    }

    // DELETE /rooms/{id}
    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return response()->json(['message' => 'Ruangan berhasil dihapus']);
    }
}
