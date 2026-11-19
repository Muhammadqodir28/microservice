<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.global', function (User $user): array {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
}, ['guards' => ['api']]);
