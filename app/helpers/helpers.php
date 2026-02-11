<?php

use Illuminate\Support\Str;

function avatarColor(string $seed): string
{
    $colors = config('avatar.colors');

    $hash = crc32($seed);
    $index = $hash % count($colors);

    return $colors[$index];
}
