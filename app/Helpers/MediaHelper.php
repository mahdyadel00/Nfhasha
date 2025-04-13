<?php

namespace App\Helpers;

use App\Models\Event;
use Illuminate\Http\Request;

function saveMedia(Request $request, Event $event, string $field = null)
{
    if ($request->hasFile('poster')) {
        $event->addMediaFromRequest('poster')->toMediaCollection('posters');
    }

    if ($request->hasFile('banner')) {
        $event->addMediaFromRequest('banner')->toMediaCollection('banners');
    }

    if ($field && $request->hasFile($field)) {
        foreach ($request->file($field) as $file) {
            $event->addMedia($file)->toMediaCollection('gallery');
        }
    }
}
