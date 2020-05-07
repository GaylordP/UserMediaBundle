<?php

namespace GaylordP\UserMediaBundle\Util;

class IsImage
{
    public static function check(string $mime): string
    {
        return in_array($mime, [
            'image/gif',
            'image/jpeg',
            'image/jpg',
            'image/pjpeg',
            'image/png',
            'image/webp',
            'image/x-webp',
        ]);
    }
}
