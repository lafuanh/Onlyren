<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'sent_at',
        'is_read'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'is_read' => 'boolean'
    ];

    protected $attributes = [
        'is_read' => false
    ];

    // Relationships
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeBetweenUsers($query, $user1Id, $user2Id)
    {
        return $query->where(function($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user1Id)
              ->where('receiver_id', $user2Id);
        })->orWhere(function($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user2Id)
              ->where('receiver_id', $user1Id);
        });
    }

    // Mutators
    public function setSentAtAttribute($value)
    {
        $this->attributes['sent_at'] = $value ? Carbon::parse($value) : now();
    }

    // Accessors
    public function getFormattedSentAtAttribute()
    {
        return $this->sent_at ? $this->sent_at->format('Y-m-d H:i:s') : null;
    }

    // Methods
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    public static function getConversation($user1Id, $user2Id, $limit = 50)
    {
        return self::betweenUsers($user1Id, $user2Id)
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->orderBy('sent_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse();
    }

    public static function getUnreadCount($userId)
    {
        return self::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    public static function getConversations($userId)
    {
        // Get all unique conversations for a user
        $conversations = self::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->orderBy('sent_at', 'desc')
            ->get()
            ->groupBy(function($message) use ($userId) {
                // Group by the other user in the conversation
                return $message->sender_id == $userId ? $message->receiver_id : $message->sender_id;
            });

        $result = [];
        foreach ($conversations as $otherUserId => $messages) {
            $latestMessage = $messages->first();
            $otherUser = $latestMessage->sender_id == $userId ? $latestMessage->receiver : $latestMessage->sender;
            
            $result[] = [
                'id' => $otherUserId,
                'user' => $otherUser,
                'last_message' => [
                    'content' => $latestMessage->message,
                    'sent_at' => $latestMessage->sent_at,
                    'is_read' => $latestMessage->is_read
                ],
                'unread_count' => self::where('sender_id', $otherUserId)
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->count()
            ];
        }

        return collect($result)->sortByDesc('last_message.sent_at')->values();
    }
} 