<!DOCTYPE html>
<html>
<head>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1" name="viewport">
  <meta name="refresh" content="60">
  <meta name="retry-after" content="100">
  <meta name="robots" content="noindex, nofollow, noarchive, nostore">
  <meta name="cache-control" content="no-cache, no-store">
  <meta name="pragma" content="no-cache">
  <title>CSGF.ru Project Maintenance</title>
  <style>
    body {
      color: #666;
      text-align: center;
      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
      top: 40%;
      left: 50%;
      position: absolute;
      transform: translate(-50%,-50%);
      font-size: 14px;
    }

    h1 {
      font-size: 56px;
      line-height: 100px;
      font-weight: normal;
      color: #456;
    }

    h2 {
      font-size: 24px;
      color: #666;
      line-height: 1.5em;
    }

    h3 {
      color: #456;
      font-size: 20px;
      font-weight: normal;
      line-height: 28px;
    }

    hr {
      max-width: 800px;
      margin: 18px auto;
      border: 0;
      border-top: 1px solid #EEE;
      border-bottom: 1px solid white;
    }

    img {
      max-width: 40vw;
    }

    .container {
      margin: auto 20px;
    }
  </style>
</head>

<body>
  <img src="data:image/svg+xml;base64,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" alt="CSGF Logo" /><br />
  <div class="container">
    <p>CSGF.ru is currently offline due to issues with code upgrading</p>
    <p>We're working hard to resolve the problem quickly. Follow <a href="https://vk.com/csgfru?w=wall-133906356_1669">Csgf|InfoPost</a> for the latest updates.</p>
    <hr />
    <a href="https://vk.com/csgfru">vk.com/csgfru</a>
  </div>
</body>
</html>
