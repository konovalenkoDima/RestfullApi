<?php

namespace App\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Photo
{
    public static function savePhoto(UploadedFile $file)
    {
        if (!is_dir(public_path("images"))){
            mkdir(public_path("images"), 0755);
        }

        $path = $file->store("images");

        self::cutPhoto($path);

        return url("/") . "/$path";
    }

    public static function cutPhoto(string $path)
    {
        \Tinify\setKey(config("tinypng.api_key"));

        $source = \Tinify\fromFile(Storage::path($path));

        $resized = $source->resize(array(
            "method" => "cover",
            "width" => 70,
            "height" => 70
        ));

        $resized->toFile(public_path("$path"));
    }
}
