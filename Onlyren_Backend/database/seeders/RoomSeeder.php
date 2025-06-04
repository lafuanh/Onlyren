<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run()
    {
        $rooms = [
            [
                'name' => 'Meeting Room A',
                'description' => 'Spacious meeting room perfect for team meetings and presentations.',
                'location' => 'Semarang',
                'type' => 'Meeting Room',
                'capacity' => '1-12',
                'specifications' => 'Whiteboard, projector, air conditioning, Wi-Fi',
                'price_per_hour' => 50000,
                'price_per_day' => 400000,
                'price_per_week' => 2500000,
                'price_per_month' => 9000000,
                'featured_image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800',
                'amenities' => ['Projector', 'Whiteboard', 'Wi-Fi', 'Air Conditioning'],
                'rating' => 4.8,
                'review_count' => 25,
                'is_available' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Co-working Space',
                'description' => 'Modern co-working space with flexible seating arrangements.',
                'location' => 'Semarang',
                'type' => 'Co-working',
                'capacity' => '1-20',
                'specifications' => 'High-speed internet, printing facilities, coffee corner',
                'price_per_hour' => 25000,
                'price_per_day' => 150000,
                'price_per_week' => 900000,
                'price_per_month' => 3500000,
                'featured_image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=800',
                'amenities' => ['High-speed Wi-Fi', 'Printing', 'Coffee', 'Flexible Seating'],
                'rating' => 4.6,
                'review_count' => 18,
                'is_available' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Conference Hall',
                'description' => 'Large conference hall suitable for seminars and events.',
                'location' => 'Semarang',
                'type' => 'Conference',
                'capacity' => '50-100',
                'specifications' => 'Stage, sound system, lighting, parking space',
                'price_per_hour' => 150000,
                'price_per_day' => 1200000,
                'price_per_week' => 7500000,
                'price_per_month' => 25000000,
                'featured_image' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800',
                'amenities' => ['Sound System', 'Lighting', 'Stage', 'Parking'],
                'rating' => 4.9,
                'review_count' => 32,
                'is_available' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Private Office',
                'description' => 'Quiet private office space for focused work.',
                'location' => 'Semarang',
                'type' => 'Private Office',
                'capacity' => '1-4',
                'specifications' => 'Desk, chairs, storage, phone line',
                'price_per_hour' => 75000,
                'price_per_day' => 500000,
                'price_per_week' => 3000000,
                'price_per_month' => 12000000,
                'featured_image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=800',
                'amenities' => ['Desk', 'Storage', 'Phone Line', 'Privacy'],
                'rating' => 4.7,
                'review_count' => 15,
                'is_available' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Training Room',
                'description' => 'Well-equipped training room for workshops and courses.',
                'location' => 'Semarang',
                'type' => 'Training',
                'capacity' => '10-30',
                'specifications' => 'Training tables, flip charts, audio visual equipment',
                'price_per_hour' => 100000,
                'price_per_day' => 800000,
                'price_per_week' => 5000000,
                'price_per_month' => 18000000,
                'featured_image' => 'https://images.unsplash.com/photo-1517502884422-41eaead166d4?w=800',
                'amenities' => ['Training Tables', 'Flip Charts', 'AV Equipment', 'Markers'],
                'rating' => 4.5,
                'review_count' => 22,
                'is_available' => true,
                'is_featured' => false,
            ]
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}