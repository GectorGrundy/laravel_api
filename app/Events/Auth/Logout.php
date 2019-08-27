<?php
declare(strict_types=1);

namespace App\Events\Auth;

use App\Models\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class Logout implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $foreign_user_hash;
    public $auth_token;

    public function __construct(User $user, string $auth_token, string $foreign_user_hash)
    {
        $this->user = $user;
        $this->auth_token = $auth_token;
        $this->foreign_user_hash = $foreign_user_hash;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['user-logout'];
    }

    /**
     * Set the name of the queue the event should be placed on.
     *
     * @return string
     */
    public function onQueue()
    {
        return config('queue.app.broadcast');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'user-logout';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'user_hash' => $this->user->hash
        ];
    }
}
