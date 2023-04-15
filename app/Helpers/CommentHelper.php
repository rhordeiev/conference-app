<?php

namespace App\Helpers;

class CommentHelper
{
    public static function transformCommentDateField(string $date): string
    {
        $dividedDateString = explode('T', $date);
        $mergedDateString = implode(
            ' ',
            [$dividedDateString[0], $dividedDateString[1]]
        );
        $dividedDateString = explode('.', $mergedDateString);

        return $dividedDateString[0];
    }
}
