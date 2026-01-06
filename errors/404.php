<?php
http_response_code(404);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>404 - Page Not Found</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            color: #333;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            padding: 20px;
        }
        .logo img {
            width: 100px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 100px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .desc {
            font-size: 18px;
            color: #555;
            max-width: 500px;
            line-height: 1.6;
        }
        .home-link {
            margin-top: 25px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #20a53a;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
        }
        .home-link:hover {
            background-color: #1e9433;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="/assets/images/logo.png" alt="Site Logo" />
        </div>
        <div class="title">404</div>
        <div class="desc">
            Sorry, the page you are looking for doesn't exist.<br>
            You might have followed a broken link or mistyped the URL.
        </div>
        <a href="/" class="home-link">Return to Homepage</a>
    </div>
</body>
</html>
