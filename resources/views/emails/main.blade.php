<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>@yield('title')</title>
  <style>
    * {
      font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
      font-size: 16px;
      line-height: 1.6em;
      margin: 0;
      padding: 0;
      color: #6d6d6d;
    }

    img {
      max-width: 600px;
      width: 100%;
    }

    body {
      -webkit-font-smoothing: antialiased;
      height: 100%;
      -webkit-text-size-adjust: none;
      width: 100% !important;
    }

    a {
      color: #348eda;
    }

    .btn-primary {
      Margin-bottom: 10px;
      width: auto !important;
    }

    .btn-primary td {
      background-color: #62cb31;
      border-radius: 3px;
      font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
      font-size: 14px;
      text-align: center;
      vertical-align: top;
    }

    .btn-primary td a {
      background-color: #62cb31;
      border: solid 1px #62cb31;
      border-radius: 3px;
      border-width: 4px 20px;
      display: inline-block;
      color: #ffffff;
      cursor: pointer;
      font-weight: bold;
      line-height: 2;
      text-decoration: none;
    }

    .last {
      margin-bottom: 0;
    }

    .first {
      margin-top: 0;
    }

    .padding {
      padding: 10px 0;
    }

    table.body-wrap {
      padding: 20px;
      width: 100%;
    }

    table.body-wrap .container {
      border: 1px solid #e4e5e7;
    }

    table.footer-wrap {
      clear: both !important;
      width: 100%;
    }

    .footer-wrap .container p, .footer-wrap .container a {
      color: #666666;
      font-size: 12px;
    }

    table.footer-wrap a {
      color: #999999;
    }

    h1,
    h2,
    h3 {
      color: #111111;
      font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
      font-weight: normal;
      line-height: 1.2em;
      margin: 15px 0;
    }

    h1 {
      font-size: 34px;
    }

    h2 {
      font-size: 26px;
    }

    h3 {
      font-size: 20px;
    }

    p,
    ul,
    ol {
      font-weight: normal;
      margin-bottom: 10px;
    }

    ul li,
    ol li {
      margin-left: 5px;
      list-style-position: inside;
    }

    /* ---------------------------------------------------
        RESPONSIVENESS
    ------------------------------------------------------ */

    /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
    .container {
      clear: both !important;
      display: block !important;
      Margin: 0 auto !important;
      max-width: 600px !important;
    }

    /* Set the padding on the td rather than the div for Outlook compatibility */
    .body-wrap .container {
      padding: 40px;
    }

    /* This should also be a block element, so that it will fill 100% of the .container */
    .content {
      display: block;
      margin: 0 auto;
      max-width: 600px;
    }

    /* Let's make sure tables in the content area are 100% wide */
    .content table {
      width: 100%;
    }

    .action_btn {
      background-color: #6d9eeb;
      padding: 12px 25px;
      color: white;
      text-decoration: none;
      font-size: 17px;
      border-radius: 3px;
      border: none;
      display: inline-block;
    }

    hr {
      display: block;
      height: 1px;
      border: 0;
      border-top: 1px solid #ccc;
      margin: 20px 0;
      padding: 0;
    }

    .m-md {
      margin: 15px 0px;
    }
  </style>
  @yield('styles')
</head>

<body bgcolor="#f7f9fa">

@yield('main')

@yield('footer')

</body>
</html>