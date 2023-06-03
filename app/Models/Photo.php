<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Photo
{

    private const BASE_URL = "https://images.abstractapi.com/v1/";

    public static function savePhoto(UploadedFile $file)
    {
        if (!is_dir(public_path("images"))){
            mkdir(public_path("images"), 0755);
        }

        $path = $file->store("images");

        $path = self::cutPhoto($path);

        return $path;
    }

    public static function cutPhoto(string $path)
    {
        $client = new Client([
            "base_uri" => self::BASE_URL
        ]);

        $api_token = config("tinypng.api_key");
        $image = fopen(Storage::path($path), "r");
        $response = $client->request("POST", "upload/", [
            "multipart" => [
                [
                    "name" => "data",
                    "contents" => json_encode([
                        "api_key" => $api_token,
                        "resize" => [
                            "width" => 70,
                            "height" => 70,
                            "strategy" => "fit"
                        ]
                    ])
                ],
                [
                    "name" => "image",
                    "contents" => $image
                ]
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $url = json_decode($response->getBody()->getContents(), 1)["url"];
            $contents = file_get_contents($url);
            $name = basename($url);
            file_put_contents(public_path("/images/" . $name), $contents);

            return url("/") . "/images/" . $name;
        } else {
            return "";
        }
    }
}
