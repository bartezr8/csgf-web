<!DOCTYPE html>
<html>
<head>
    <title>404 not found.</title>

    <link href="//fonts.googleapis.com/css?family=Roboto:100" rel="stylesheet" type="text/css">

    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: Comfortaa;
            padding: 0;
            width: 100%;
            font-size: 14px;
            color: #333;
            display: table;
            font-weight: 100;
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .title {
            font-size: 72px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title"><img src="/assets/img/404_image.png"></div>
        <a class="btn btn-primary" href="{{ route('index') }}">На главную</a>
    </div>
</div>
</body>
</html>
