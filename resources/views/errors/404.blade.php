<!DOCTYPE html>
<html>
<head>
    <title>404 | CSGF.RU</title>
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <!--link href="//fonts.googleapis.com/css?family=Roboto:100" rel="stylesheet" type="text/css"-->

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
        a.green{
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.08);
        -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.08);
        color: #fff;
        display:block;
        width:100px;
        text-align: center;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
        padding: 8px 16px;
        margin: 20px auto;
        text-decoration: none;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.075);
        -webkit-transition: background-color 0.1s linear;
        -moz-transition: background-color 0.1s linear;
        -o-transition: background-color 0.1s linear;
        transition: background-color 0.1s linear;        
        }
        a.green {
        background-color: rgb( 43, 153, 91 );
        border: 1px solid rgb( 33, 126, 74 );
        }
                
        a.green:hover {
        background-color: rgb( 75, 183, 141 );
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title"><img src="/assets/img/404_image.png"></div>
        <a class="green" href="{{ route('index') }}">На главную</a>
    </div>
</div>
</body>
</html>
