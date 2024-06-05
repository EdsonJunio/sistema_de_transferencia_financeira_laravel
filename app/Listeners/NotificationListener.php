<?php

namespace App\Listeners;

use App\Events\SendNotification;
use App\Services\MockyService;

class NotificationListener
{
    private MockyService $mockyService;

    public function __construct(MockyService $mockyService)
    {
        $this->mockyService = $mockyService;
    }

    public function handle(SendNotification $event): void
    {
        $this->sendNotification($event);
    }

    private function sendNotification(SendNotification $event): void
    {
        $userId = $event->transaction->user->id;
        $this->mockyService->notifyUser($userId);
    }
}
