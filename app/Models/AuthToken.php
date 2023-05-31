<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthToken extends Model
{
    use HasFactory;

    protected $fillable = [
        "token",
        "expires_at"
    ];

    public const INCORRECT_TOKEN = "incorrect token";

    public const TOKEN_EXPIRED = "token expire";

    public const TOKEN_OK = "ok";

    public static function checkToken(string $token)
    {
        $token = self::firstWhere("token", "=", $token);

        if (is_null($token)) {
            return [
                "status" => self::INCORRECT_TOKEN
            ];
        } else if ($token->expires_at < now()->format("Y-m-d h:i:s")){
            $token->delete();
            return [
                "status" => self::TOKEN_EXPIRED
            ];
        } else {
            return [
                "status" => self::TOKEN_OK
            ];
        }
    }

    public static function removeToken(string $token)
    {
        self::firstWhere("token", "=", $token)->delete();
    }
}
