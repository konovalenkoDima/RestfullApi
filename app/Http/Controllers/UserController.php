<?php

namespace App\Http\Controllers;

use App\Models\AuthToken;
use App\Models\Photo;
use App\Models\User;
use App\Rules\CheckPhoneFormat;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getToken(Request $request)
    {
        $tokenStr = Hash::make(\Nette\Utils\Random::generate(20));
        $token = new AuthToken();
        $token->token = $tokenStr;
        $token->expires_at = now()->addMinutes(40)->format('Y-m-d H:i:s');
        $token->save();

        return response([
            "success" => true,
            'token' => $tokenStr
        ], Response::HTTP_OK);
    }

    public function searchUser($id)
    {
        if (!is_int((int)$id)) {
            return response([
                "success" => false,
                "message" => "Validation failed",
                "fails" => [
                    "user_id" => [
                        "The user_id must be an integer."
                    ]
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = User::find($id);

        if (is_null($user)) {
            return response([
                "success" => false,
                "message" => "The user with the requested identifier does not exist",
                "fails" => [
                    "user_id" => [
                        "User not found"
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        return response([
            "success" => true,
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "phone" => $user->phone,
                "position" => $user->position->name,
                "position_id" => $user->position_id,
                "photo" => $user->photo,
                "registration_timestamp" => $user->registration_timestamp
            ]
        ]);
    }

    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => ["required", "string"],
        ], [
            "required" => "The :attribute field is required.",
        ]);

        if ($validator->fails()) {
            return response([
                "success" => false,
                "message" => "Validation failed",
                "fails" => $validator->messages()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->sendIncorrectTokenResponse($request->input("token"));

        if (User::where("phone", "=", $request->input("phone"))
            ->orWhere("email", "=", $request->input("email"))
            ->first()) {

            return response([
                "success" => false,
                "message" => "User with this phone or email already exist"
            ], Response::HTTP_CONFLICT);
        }

        $this->validator($request);

        $user = User::create([
            "name" => $request->input("name"),
            "email" => $request->input("email"),
            "phone" => $request->input("phone"),
            "position_id" => $request->input("position_id"),
            "photo" => Photo::savePhoto($request->file("photo"))
        ]);

        AuthToken::removeToken($request->input("token"));

        return response([
            "success" => true,
            "user_id" => $user->id,
            "message" => "New user successfully registered"
        ]);
    }

    public function getUsers(Request $request)
    {
        $page = $request->input("page") ?? "";

        $countPerPage = $request->input("count") ?? "";

        $validator = Validator::make($request->all(), [
            "page" => ["required", "int", "min:1"],
            "count" => ["required", "int", "min:1"],
        ], [
            "required" => "The :attribute must be at least 1.",
            "min" => "The :attribute must be at least 1.",
            "int" => "The :attribute must be an integer.",
        ]);

        if ($validator->fails()) {
            return response([
                "success" => false,
                "message" => "Validation failed",
                "fails" => $validator->messages()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        $users = User::with("position")
            ->select("id", "name", "email", "phone", "position_id", "registration_timestamp", "photo")
            ->orderBy("id")->limit($countPerPage)
            ->skip(($page - 1) * $countPerPage)
            ->get()->toArray();

        $usersCount = count($users);

        $total_pages = ceil($usersCount / $page);

        return response([
            "success" => true,
            "page" => $page,
            "total_pages" => $total_pages,
            "total_users" => $usersCount,
            "count" => $countPerPage,
            "links" => [
                "next_url" => $page != $total_pages ? url("/") . "/api/v1/users?page=" . ++$page . "&count=$countPerPage" : null,
                "prev_url" => --$page != 1 ? url("/") . "/api/v1/users?page=" . --$page . "&count=$countPerPage" : null
            ],
            "users" => $users
        ]);
    }

    public function validator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => ["required", "string", "max:60", "min:2"],
            "email" => ["required", "email", "min:2", "max:100"],
            "phone" => ["required", new CheckPhoneFormat()],
            "position_id" => ['required', "integer", "min:1"],
            "photo" => ['required', "image:jpeg, jpg", "max:5120"]
        ], [
            "required" => "The :attribute field is required.",
            "min" => "The :attribute must be at least 2 characters.",
            "name.max" => "The name may not be longer than 60 characters",
            "email.max" => "The email may not be longer than 100 characters",
            "position_id.min" => "The position id must be at least 1 character.",
            "position_id.integer" => "The position id must be an integer.",
            "name.string" => "The name must be an string.",
            "email.email" => "The email must be a valid email address.",
            "photo.max" => "The photo may not be greater than 5 Mbytes.",
            "photo.image" => "Image is invalid.",
        ]);

        if ($validator->fails()) {
            return response([
                "success" => false,
                "message" => "Validation failed",
                "fails" => $validator->messages()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function sendIncorrectTokenResponse(string $token = "")
    {
        if (empty($token)) {
            return response([
                "success" => false,
                "message" => "The token is incorrect"
            ], Response::HTTP_UNAUTHORIZED);
        }
        $checkToken = AuthToken::checkToken($token);

        switch ($checkToken["status"]) {
            case AuthToken::INCORRECT_TOKEN:
                response([
                    "success" => false,
                    "message" => "The token is incorrect"
                ], Response::HTTP_UNAUTHORIZED);
            case AuthToken::TOKEN_EXPIRED:
                response([
                    "success" => false,
                    "message" => "The token expired."
                ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
