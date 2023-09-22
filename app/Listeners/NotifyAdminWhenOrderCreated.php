<?php

namespace App\Listeners;

use App\Enums\QueuePriority;
use App\Events\OrderCreated;
use App\Mail\OrderCreatedNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAdminWhenOrderCreated
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        $emailAdmins = $this->getEmailAdmins();

        foreach ($emailAdmins as $email) {
            $message = (new OrderCreatedNotification($order))
                ->onQueue(QueuePriority::EMAIL);

            Mail::to($email)->queue($message);
        }
    }

    protected function getEmailAdmins(): array
    {
        return User::whereHas('roles', function ($query) {
            return $query->where('name', 'admin');
        })
            ->get()
            ->pluck('email')
            ->toArray();
    }
}
