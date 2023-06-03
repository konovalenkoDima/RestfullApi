@php
    $page = 1;
@endphp
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"
            integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V"
            crossorigin="anonymous"></script>
</head>
@vite("resources/js/app.js")
<body class="antialiased">

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#getUsers" type="button"
                role="tab" aria-controls="getUsers" aria-selected="true">Users
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#getPositions" type="button"
                role="tab" aria-controls="getPositions" aria-selected="false">Positions
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#getUserByIdTab" type="button"
                role="tab" aria-controls="getUserByIdTab" aria-selected="false">Get User by id
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="disabled-tab" data-bs-toggle="tab" data-bs-target="#createUser" type="button"
                role="tab" aria-controls="createUser" aria-selected="false">Create user
        </button>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="getUsers" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">id</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Position</th>
                <th scope="col">Position id</th>
                <th scope="col">Photo</th>
                <th scope="col">Registration at</th>
            </tr>
            </thead>
            <tbody id="table_body">

            </tbody>
        </table>
        <button id="moreUsers" class="btn btn-info" data-source="1">Show more</button>
    </div>
    <div class="tab-pane fade" id="getPositions" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">id</th>
                <th scope="col">Name</th>
            </tr>
            </thead>
            <tbody id="positions_table_body">

            </tbody>
        </table>
        <button id="getPositions" class="btn btn-info" data-source="1">Get positions</button>
    </div>
    <div class="tab-pane fade" id="getUserByIdTab" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
        <input type="number" placeholder="Enter user id" id="userIdInput">
        <button id="getUserById" class="btn btn-info" data-source="1">Get user</button>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">id</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Position</th>
                <th scope="col">Position id</th>
                <th scope="col">Photo</th>
                <th scope="col">Registration at</th>
            </tr>
            </thead>
            <tbody id="userById_table_body">

            </tbody>
        </table>
    </div>
    <div class="tab-pane fade" id="createUser" role="tabpanel" aria-labelledby="disabled-tab" tabindex="0">
        <button id="getToken" class="btn btn-info" data-source="1">Get token</button>
        <form action="{{route("create.user")}}" method="post" class="form" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <input type="text" name="name" placeholder="Enter name">
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Enter email">
            </div>
            <div class="form-group">
                <input type="text" name="phone" placeholder="Enter phone">
            </div>
            <div class="form-group">
                <input type="number" name="position_id" placeholder="Enter position_id">
            </div>
            <div class="form-group">
                <input id="createToken" type="hidden" name="token">
            </div>
            <div class="form-group">
                <label for="photo">Load your photo</label>
                <input id="photo" type="file" name="photo">
            </div>

            <button class="btn btn-info" type="submit">Create user</button>
        </form>
    </div>
</div>

<script>
    var moreUsersBtn = document.querySelector("#moreUsers");
    var getPositionsBtn = document.querySelector("#getPositions");
    var getUserByIdBtn = document.querySelector("#getUserById");
    var getTokenBtn = document.querySelector("#getToken");
    var table_body = document.querySelector("#table_body");
    var createToken = document.querySelector("#createToken");
    var positions_table_body = document.querySelector("#positions_table_body");
    var userById_table_body = document.querySelector("#userById_table_body");
    var page = moreUsersBtn.getAttribute("data-source")


    moreUsersBtn.addEventListener("click", function () {
        axios.get("/api/v1/users?page=" + page++ + "&count=5")
            .then(function (response) {
                moreUsersBtn.setAttribute("data-source", page)
                response.data.users.forEach(user => {
                    table_body.innerHTML = table_body.innerHTML + '<tr>' +
                        '<th scope="row">' + user.id + '</th>' +
                        '<td>' + user.name + '</td>' +
                        '<td>' + user.email + '</td>' +
                        '<td>' + user.phone + '</td>' +
                        '<td>' + user.position.name + '</td>' +
                        '<td>' + user.position_id + '</td>' +
                        '<td><img src="' + user.photo + '" alt="Image"></td>' +
                        '<td>' + user.registration_timestamp + '</td>' +
                        '</tr>'
                })
            })
    });

    getPositionsBtn.addEventListener("click", function () {
        axios.get("{{route("get.positions")}}")
            .then(function (response) {
                response.data.positions.forEach(position => {
                    positions_table_body.innerHTML = positions_table_body.innerHTML + '<tr>' +
                        '<th scope="row">' + position.id + '</th>' +
                        '<td>' + position.name + '</td>' +
                        '</tr>'
                })
            })
    });

    getTokenBtn.addEventListener("click", function () {
        axios.get("{{route("get.token")}}")
            .then(function (response) {
                createToken.value = response.data.token;
            })
    });

    getUserByIdBtn.addEventListener("click", function () {
        var userId = document.querySelector("#userIdInput").value;

        axios.get("/api/v1/users/" + userId)
            .then(function (response) {
                if (response.data.status === false)
                {
                    return;
                }

                var user = response.data.user;
                userById_table_body.innerHTML = userById_table_body.innerHTML + '<tr>' +
                    '<th scope="row">' + user.id + '</th>' +
                    '<td>' + user.name + '</td>' +
                    '<td>' + user.email + '</td>' +
                    '<td>' + user.phone + '</td>' +
                    '<td>' + user.position + '</td>' +
                    '<td>' + user.position_id + '</td>' +
                    '<td><img src="' + user.photo + '" alt="Image"></td>' +
                    '<td>' + user.registration_timestamp + '</td>' +
                    '</tr>'
            })
    });
</script>
</body>
</html>
