<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserBalanceChanged extends Event implements ShouldBroadcast
{
	use SerializesModels;

	public $user_id;

	public function __construct(int $user_id)
	{
		$this->user_id = $user_id;
	}

	/**
	 * Get the channels the event should be broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn()
	{
        return new PrivateChannel('balance-changed-' . $this->user_id);
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
	 * Get the data to broadcast.
	 *
	 * @return array
	 */
	public function broadcastWith()
	{
        $user = User::find($this->user_id);
        $user::$role = $user['role'];
        $user->load('profile');

		return [
			'user_hash' => $user->hash,
			'balance_rub' => $user->profile['balance_rub'],
			'balance_usd' => $user->profile['balance_usd'],
			'balance_eur' => $user->profile['balance_eur'],
			'hold_rub' => $user->profile['hold_rub'],
			'hold_usd' => $user->profile['hold_usd'],
			'hold_eur' => $user->profile['hold_eur'],
		];
	}
}
