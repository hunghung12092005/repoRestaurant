<?php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    // Chỉ cho phép user lắng nghe trên kênh của chính mình
    return (int) $user->id === (int) $id;
});