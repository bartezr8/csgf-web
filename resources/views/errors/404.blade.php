<!DOCTYPE html>
<html>
<head>
    <title>404 | CSGF.RU</title>
    <meta charset="utf-8" />
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <!--link href="//fonts.googleapis.com/css?family=Roboto:100" rel="stylesheet" type="text/css"-->

    <style>
        html, body {
            height: 100%;
            background: url("/assets/img/background.png") repeat !important;
            position: fixed;
            width: 100%;
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
        a.bot8 {
            background-color: #FFFFFF;
            border: 1px solid #CCCCCC;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;
            transition: border 0.2s linear 0s, box-shadow 0.2s linear 0s;
            border-radius: 4px;
            color: #555555;
            display:block;
            width:120px;
            margin: 20px auto;
            font-size: 14px;
            text-align:center;
            line-height: 20px;
            margin-bottom: 10px;
            padding: 4px 6px;
            vertical-align: middle;
            text-decoration:none;
        }
        a.bot8:hover, a.bot8:focus {
           border-color: rgba(82, 168, 236, 0.8);
           box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset, 0 0 8px rgba(82, 168, 236, 0.6);
           outline: 0 none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title"><img src="/assets/img/404_image.png"></div>
        <a class="bot8" href="{{ route('index') }}">На главную</a>
    </div>
</div>
</body>
</html>
