<style>
  body {
    padding: 0;
    margin: 0;
    background: rgba(245, 245, 245, 0.3);
  }

  body {
    font-family: Lato, arial, verdana, tahoma, serif;
    font-size: 13px;
    color: #707070;
  }

  *, :after, :before {
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
  }

  * {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none
  }

  .column_left, .right_column {
    float: left;
    margin-top: 20px;
  }

  .column_left {
    width: 622px;
  }

  .right_column {
    width: 319px;
    margin: 36px 0 0 0;
  }

  .right_column .s1 {
    font-family: arial, sans-serif !important;
    line-height: 22px !important;
    padding: 0 0 8px 30px !important;
    color: black;
    font-size: 16px;
  }

  .s1 b {
    font-weight: bold;
    color: black;
    font-size: 16px;
  }

  .clear, .cl {
    clear: both;
  }

  section {
    width: 941px;
    margin: 0px auto;
    position: relative;
  }

  header .logo {
    width: 236px;
    top: 35px;
    left: 20px;
  }

  header .logo img {
    width: 100%;
  }

  .hr {
    height: 2px;
    background: black;
  }

  .header_description {
    margin-top: 20px;
  }

  .header_description h2, .header_description h2 * {
    font-size: 24px;
  }

  .twoBoxes .tLeft strong, .twoBoxes .tRight strong {
    font-size: 15px;
  }

  h3.box_title {
    background: #2D4467;
    padding: 8px 10px;
    font-size: 18px;
    color: white;
    margin: 10px 0;
    font-weight: 700;
    border-radius: 3px;
  }

  h3 * {
    font-size: inherit;
    color: #59B1B6;
    font-weight: normal;
  }

  h2 {
    color: #59B1B6;
    margin: -10px 0px 15px 0px;
    font-size: 16px;
  }

  .items_ul {
    display: block;
    margin: 0px;
    padding: 0px;
    list-style: none;
  }

  .items_ul li {
    display: block;
    position: relative;
    padding: 0 20px 20px 0;
    border: 1px solid #d0d0d0;
    margin-bottom: 10px;
    transition: all 0.3s;
    cursor: pointer;
    background-color: #fff;
  }

  .items_ul li .img img {
    max-width: 100%;
    max-height: 100%;
  }

  .items_ul li .img {
    position: absolute;
    left: 50px;
    width: 115px;
    top: 50%;
    margin-top: -4.6em;
    height: 127px;
    text-align: center;
  }

  .payment_box h3 {
	  margin-bottom: 15px;
  }

  .items_ul li.recommended, .items_ul li.selected {
    background-color: #f4ffff;
    border-color: #59b1b6;
  }

  .items_ul li .information {
    margin-left: 180px;
  }

  .items_ul li .information p {
    line-height: 18px;
    padding: 10px 0;
  }

  .items_ul li .information h4 {
    margin: 20px 0 0 0;
    text-transform: uppercase;
    font-size: 17px;
    color: #2D4467;
  }

  .items_ul li .information h4 * {
    font-size: inherit;
    color: inherit;
  }

  .items_ul li .select {
    position: absolute;
    top: 89px;
    left: 20px;
    display: block;
    width: 18px;
    height: 18px;
    padding-top: 0 !important;
    border-radius: 90px;
    border: 1px solid #59b1b6;
    z-index: 1090;
  }

  .items_ul li .select span {
    display: block;
    width: 10px;
    height: 10px;
    border-radius: 90px;
    margin: 4px 0px 0px 4px;
  }

  .items_ul li.selected .select span {
    background: #2D4467;
  }

  .items_ul li.payment_method {
    background-image: none;
  }

  .items_ul li.payment_method .information {
    margin-left: 20px;
  }

  .items_ul li.payment_method .information h3 {
    background: none;
    padding: 0 0 0 30px;
    color: #2D4467;
    font-size: 16px;
  }

  .items_ul li.payment_method .select {
    top: 11px;
    left: 20px;
  }

  .items_ul li.payment_method br, .items_ul li.payment_method img {
    max-width: 100%;
    float: right;
  }

  .packUpsell li .select {
    border-radius: 6px;
    font-size: 31px;
  }

  .packUpsell li.selected .select .checkbox {
    position: absolute;
    top: -14px;
    left: -8px;
    background: none;
  }

  .checkout_forms_wrap {
    width: 941px;
  }

  .pay_box {
    float: left;
    width: 32.5%;
    margin-right: 10px;
  }

  .pay_box:nth-child(3) {
    margin-right: 0px;
    width: 32.7%;
  }

  .pay_box {
    float: left;
    width: 32.5%;
    margin-right: 10px;
  }

  select.styled {
    position: relative;
    width: 190px;
    opacity: 0;
    filter: alpha(opacity=0);
    z-index: 5;
  }

  .checkout_forms_wrap .reguired {
    display: none;
  }

  #form_order ul {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  #form_order .form-item {
    display: block;
    float: left;
  }

  #form_order form label {
    font-size: 14px;
  }

  #form_order .form-item input, #form_order .form-item select {
    border: 1px solid #C7C7C9;
    background: white;
    font-size: 15px;
    padding: 4px 6px;
    width: 97%;
    border-radius: 3px;
  }

  #form_order .form-item select {
    height: 27px;
  }

  #form_order .email,
  #form_order .name,
  #form_order .last_name,
  #form_order .street,
  #form_order .document,
  #form_order .target_geo_region_id {
    width: 100%;
  }

  #form_order .form-item.house {
    width: 47%;
    margin-right: 3%;
  }

  #form_order .form-item.apartment {
    width: 49%;
  }

  #form_order .form-item.zipcode {
    width: 47%;
    margin-right: 3%;
  }

  #form_order .form-item.city {
    width: 49%;
  }

  #form_order .form-item.phone {
    width: 47%;
  }

  #form_order .form-item .inputdesc {
    color: #67B6BB;
    font-size: 10px;
    margin-top: 14px;
    display: block;
    cursor: pointer;
  }

  .latin_box {
    background-color: #cf0001;
    border: 1px solid black;
    margin: 0 0 10px;
    padding: 3px 10px;
    text-align: center;
  }

  .latin_box span {
    font-size: 14px;
    font-weight: bold;
    color: #fff;
  }

  #form_order .form-item.error label {
    color: red;
  }

  .fa {
    width: 15px;
  }

  label {
    cursor: pointer;
  }

  .summary {
    font-size: 16px;
    font-weight: bold;
  }

  .summary span {
    color: #000;
  }

  .summary ul {
    display: block;
    margin: 0px;
    padding: 0px;
    list-style: none;
  }

  .summary ul li {
    display: block;
    margin: 0;
    padding: 0;
  }

  .summary ul li .input {
    font-weight: normal;
    margin-bottom: 20px;
    line-height: 17px;
    font-size: 15px;
  }

  .no_risk_wrap {
    margin-top: 10px;
    background: url('data:image/png;base64,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') no-repeat 0px 0px;
    padding: 0 0 0 25px;
    font-size: 12px;
	  min-height: 22px;
	  line-height: 22px;
  }

  .processing .input {
    cursor: pointer;
    transition: all .6s;
  }

  .processing .input:hover {
    color: black;
    cursor: pointer;
  }

  .processing .input .req {
    display: none;
    transition: all .3s;
    color: red;
  }

  .processing.selected .input .req {
    opacity: 1;
  }

  footer {
    background: #F8F8F8;
    padding: 30px 0px 30px 0px;
  }

  footer section ul {
    display: block;
    margin: 0px;
    padding: 0px;
    list-style: none;
  }

  footer section ul li {
    display: block;
    margin: 0px;
    padding: 0px;
    float: left;
    margin-right: 10px;
  }

  footer section ul li a {
    text-transform: uppercase;
  }

  footer section .copyright {
    float: right;
    text-transform: uppercase;
  }

  .submit_btn {
    display: block;
    border-radius: 2px;
    border: none;
    font: bold 18px arial, verdana;
    color: #334467;
    padding: 20px 0px;
    margin: 20px 0;
    width: 100%;
    text-align: center;
    cursor: pointer;
    background: #f3d753;
  }

  .submit_btn:hover:not(:disabled) {
    background: #e8b425;
  }

  .error_message_wrap {
    position: fixed;
    z-index: 999888;
    background: rgba(0, 0, 0, 0.7);
    border-radius: 5px;
    text-align: center;
    top: -80px;
    left: 50%;
    width: 400px;
    height: 38px;
    color: white;
    font: bold 17px arial, verdana;
    padding: 34px 0px 0px 0px;
    margin-left: -200px;
    box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.5);
  }

  .recommend_package {
    position: absolute;
    right: 0;
    top: 0;
    color: #fff;
    background: #EEA124;
    padding: 4px 6px;
    font-size: 12px;
  }

  /* info box */
  .infoBg {
    background: rgba(0, 0, 0, 0.5);
    z-index: 9998;
    width: 100%;
    height: 100%;
    position: fixed;
    display: none;
    left: 0px;
    top: 0px;
  }

  .infoBox {
    position: fixed;
    width: 500px;
    height: 200px;
    top: 50%;
    left: 50%;
    margin-left: -250px;
    margin-top: -100px;
    background: red;
    z-index: 9999;
    display: none;
    border-radius: 5px;
    background: #fcfdfd; /* Old browsers */
    /* IE9 SVG, needs conditional override of 'filter' to 'none' */
    background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2ZjZmRmZCIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNmZGZlZmUiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
    background: -moz-linear-gradient(top, #fcfdfd 0%, #fdfefe 100%); /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #fcfdfd), color-stop(100%, #fdfefe)); /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, #fcfdfd 0%, #fdfefe 100%); /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, #fcfdfd 0%, #fdfefe 100%); /* Opera 11.10+ */
    background: -ms-linear-gradient(top, #fcfdfd 0%, #fdfefe 100%); /* IE10+ */
    background: linear-gradient(to bottom, #fcfdfd 0%, #fdfefe 100%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fcfdfd', endColorstr='#fdfefe', GradientType=0); /* IE6-8 */
  }

  .infoBox .mhead {
    padding: 10px 20px;
    background: green;
    margin: 2px 2px 10px 2px;
    border-radius: 5px;
    font-weight: bold;
    position: relative;

    background: #b8e2e5; /* Old browsers */
    /* IE9 SVG, needs conditional override of 'filter' to 'none' */
    background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2I4ZTJlNSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiM5Y2Q3ZGMiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
    background: -moz-linear-gradient(top, #b8e2e5 0%, #9cd7dc 100%); /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #b8e2e5), color-stop(100%, #9cd7dc)); /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, #b8e2e5 0%, #9cd7dc 100%); /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, #b8e2e5 0%, #9cd7dc 100%); /* Opera 11.10+ */
    background: -ms-linear-gradient(top, #b8e2e5 0%, #9cd7dc 100%); /* IE10+ */
    background: linear-gradient(to bottom, #b8e2e5 0%, #9cd7dc 100%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#b8e2e5', endColorstr='#9cd7dc', GradientType=0); /* IE6-8 */

  }

  .infoBox .mhead .fa-times {
    display: block;
    position: absolute;
    top: 2px;
    right: 2px;
    color: white;
    text-decoration: none;
    padding: 3px;
    border: 1px solid rgba(255, 255, 255, 0);
    border-radius: 2px;
    font-size: 20px;
    transition: border .3s, color 0.6s;
  }

  .infoBox .mhead .fa-times:hover {
    border: 1px solid rgba(255, 255, 255, 1);
    color: black;
  }

  .infoBox .mcontent {
    padding: 10px 10px;
  }

  .massCollectDisallowed {
    color: red;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
  }

  #form_order .form-item input {
    position: relative;
    display: block;
  }

  #form_order .form-item input:focus {
    background: #EDF7F9;
    border-color: #3E98B8;
  }

  #form_order .form-item.error input, #form_order .form-item.error select {
    border: 1px solid red;
  }

  @-webkit-keyframes inputUnfocus {
    0% {
      -webkit-transform: scale(1);
      opacity: 1;
    }
    95% {
      -webkit-transform: scale(1.1);
      opacity: 0.5;
    }
    100% {
      -webkit-transform: scale(1);
      opacity: 1;
    }
  }

  @keyframes inputUnfocus {
    0% {
      transform: scale(1);
      opacity: 1;
    }
    95% {
      transform: scale(1.1);
      opacity: 0.5;
    }
    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  @media screen and (max-width: 959px) {

    body {
      padding-top: 0px
    }

    div.zawartosc {
      max-width: 100%;
      padding: 3%;
      box-sizing: border-box
    }

    h1.new {
      position: relative;
      margin: 0;
      left: 0;
      top: 0;
      width: 100%;
      background-position: 0 0;

      height: 325px
    }

    .arrow {
      display: none
    }

    #fr-form {
      width: 92% !important;
      max-width: 580px !important;
      float: none !important;
      padding: 10px !important;
      margin: 0px auto;
      margin-right: auto !important
    }

    #bundle {
      margin-bottom: 2em
    }

    .zawartosc h2 {
      padding: 0
    }

    p {
      padding: 10px 0
    }

    ul {
      padding: 20px 0 0 20px
    }

    section {
      max-width: 941px;
      width: 100%;
      margin: 0px auto;
      position: relative;
    }

    .right_column {
      display: none
    }

    .column_left {
      width: 94%;
      margin: 20px auto 0 auto;
      float: none;
    }

    .checkout_forms_wrap {
      width: 94%;
      margin: 0px auto;
      float: none
    }

    #form_order ul li.apartment {
      width: 49%;
    }

    .pay_box:nth-child(3) {
      width: 100%
    }

    .pay_box {
      width: 100%
    }

    .header_description p {
      padding: 0
    }

    .header_description h2 {
      font-size: 20px
    }

    #form_order ul {
      margin-bottom: 0.5em
    }
  }

  .payment_method:after {
    content: '';
    display: block;
    position: absolute;
    bottom: 0px;
    right: 5px;
    z-index: 9;
    border-radius: 6px;
    padding: 6px 10px !important
  }

  .order_form {
    background: #f3f3f3 none repeat scroll 0 0;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-family: Arial, Helvetica;
    margin: 0;
    padding: 25px;
  }

  .block_form {
    margin: 0 0 14px;
  }

  .block_form_label {
    width: 65px;
    font-size: 14px;
    color: #333;
    text-align: right;
    display: block;
    float: left;
    margin: 8px 10px 0 0;
  }

  .block_form input, .block_form select {
    display: block;
    width: 100%;
    height: 50px;
    text-indent: 20px;
    border: #888 1px solid;
    font-size: 18px;
    line-height: 50px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    background: #fff;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
  }

  .block_form select {
    padding: 2px 0;
    margin-top: 6px;
    font-size: 16px;
  }

  .block_form p {
    font-size: 15px;
    padding-top: 8px;
    color: #1D1D1D;
  }

  .form_hr {
    border-top: #bbb 1px solid;
  }

  .block_form div {
    position: relative;
  }

  .block_form_off {
    display: none;
  }

  .w_block_form_prices {
  }

  .block_form_prices {
  }

  .block_form_prices p {
    margin-top: 3px;
    margin-bottom: 18px;
  }

  .block_form_prices_label {
    padding-right: 5px;
  }

  .block_form_prices_total {
  }

  .block_form_prices_total label {
    text-transform: uppercase;
  }

  .block_form_prices_total p {
    font-size: 16px;
    padding-top: 20px;
    margin-bottom: 0px;
    font-weight: 400;
    color: #aaa;
    text-align: center;
  }

  .order_form .clear {
    clear: both;
  }

  .order_form .fhelp {
    font-size: 14px;
    color: #929292;
    display: block;
    padding-top: 10px;
  }

  .errorMessage {
    position: absolute;
    z-index: 10000;
    font-size: 14px;
    background: #e74c3c;
    color: #fff;
    padding: 6px 7px 5px;
    margin: 3px 0px 0px 1px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    font-family: Arial;
  }

  @media screen and (max-width: 710px) {
    .block_form {
      margin-bottom: 14px;
    }

    .block_form label {
      text-align: left;
      margin: 8px 21px 7px 0;
      float: none;
    }

    .block_form div {
      margin-left: 0px;
    }

    .block_form_prices label {
      float: left;
      width: auto
    }

    .int_price_total {
      margin: 0px;
    }

    .w_block_form_prices {
      padding-left: 0px;
    }
  }

  @media screen and (max-width: 380px) {
    .products_wrap .items_ul li .select {
      top: 20px
    }

    .products_wrap .items_ul li .information h4 {
      position: absolute;
      top: -3px;
      left: 46px;
    }

    .products_wrap .items_ul li .img {
      top: 130px;
      left: 0px;
      width: 100%;
      text-align: center;
    }

    .products_wrap .items_ul li .information {
      margin-left: 20px;
      margin-top: 209px;
    }
  }

  .requisites {
    text-align: center;
    font-size: 12px;
    margin-top: 25px;
  }

  .requisites a {
    color: gray;
    text-decoration: none;
    padding-top: 5px;
  }

  .requisites a:hover {
    color: rgb(14, 174, 220);
    text-decoration: none;
  }

  .sub-phone-question {
    font-size: 10px;
    margin-top: 2px;
    display: block;
    cursor: pointer;
  }

  .modal-answer {
    position: fixed;
    width: 500px;
    top: 50%;
    left: 50%;
    margin-left: -250px;
    margin-top: -100px;
    z-index: 9998;
    display: none;
    border-radius: 5px;
    background: #fcfdfd;
  }

  .modal-title {
    padding: 10px 20px;
    margin: 2px 2px 0 2px;
    border-radius: 5px;
    font-weight: bold;
    position: relative;
    background: #b8e2e5;
  }

  .modal-wraper {
    background: rgba(0, 0, 0, 0.5);
    z-index: 9997;
    width: 100%;
    height: 100%;
    position: fixed;
    display: none;
    left: 0px;
    top: 0px;
  }

  .modal-text {
    padding: 10px 20px;
  }

  .main-modal-div {
    display: inline-block;
  }

  .main-modal-div .requsites-phone-img {
    display: block;
    position: absolute;
    top: 2px;
    right: 2px;
    color: white;
    text-decoration: none;
    padding: 3px;
    border: 1px solid rgba(255, 255, 255, 0);
    border-radius: 2px;
    font-size: 20px;
    transition: border .3s, color 0.6s;
    z-index: 9999;
    cursor: pointer;
  }

  .main-modal-div .requsites-phone-img:hover {
    border: 1px solid rgba(0, 0, 0, 1);
    color: black;
  }

  .requsites-phone-img {
    vertical-align: bottom;
    width: 30px;
  }

  @media (max-width: 540px) {
    .modal-answer {
      width: 300px;
      margin-left: -150px;
    }

    .modal-title {
      padding: 10px 20px;
      font-size: 12px;
    }
  }

  .box {
    padding: 10px;
    border: 1px solid #d0d0d0;
    transition: all 0.3s;
    background-color: #fff;
  }

  .summary_item {
    border-bottom: 1px solid #eee;
    padding: 5px 0 10px 0;
  }

  .summary_item .summary_item_value {
    float: right;
  }

  .summary_item.summary_item_total {
    font-size: 18px;
    border-bottom: none;
  }

  .summary_product_title {
    font-size: 15px;
    padding-bottom: 20px;
  }

  #policy_error {
    margin-top: 10px;
    display: none;
    color: #cf0001;
  }

  .policy_wrap {
    padding: 15px 0 0 0;
    font-size: 13px;
  }

  .clearfix {
    content: "";
    display: table;
    clear: both;
  }

  #form_order .form-item {
    margin-top: 7px;
  }

  #country_selection {
    display: none;
  }

  .hidden {
    display: none !important;

  }

  #country_selection_result {
    display: block;
    font-size: 15px;
  }

  #country_selection_result small {
    font-size: 70%;
    border-bottom: 1px solid #c1c1c1;
  }

  #change_delivery_country {
    cursor: pointer;
  }

  .items_ul li .information .stroke {
    text-decoration: line-through;
    color: #666;
  }

  .products .pDown {
    font-size: 20px;
  }

  a {
    color: #607a98 !important;
  }

  .payment_box {
    padding: 50px 100px;
    text-align: center;
  }

  .payment_box .wrap {
    margin: 0 auto;
    display: inline-block;
  }

  .payment_box .message {
    font-size: 18px;
    float: left;
    padding: 0 0 0 20px;
  }

  .loader {
    color: #000000;
    font-size: 20px;
    text-indent: -9999em;
    overflow: hidden;
    width: 1em;
    height: 1em;
    border-radius: 50%;
    position: relative;
    -webkit-transform: translateZ(0);
    -ms-transform: translateZ(0);
    transform: translateZ(0);
    -webkit-animation: load6 1.7s infinite ease;
    animation: load6 1.7s infinite ease;
    float: left;
  }

  .loader {
	  display: block;
	  clear: both;
	  float: none;
	  margin: 12px auto;
  }

  .message-succsess {
		font-size: 18px;
  }

  @-webkit-keyframes load6 {
    0% {
      -webkit-transform: rotate(0deg);
      transform: rotate(0deg);
      box-shadow: 0 -0.83em 0 -0.4em, 0 -0.83em 0 -0.42em, 0 -0.83em 0 -0.44em, 0 -0.83em 0 -0.46em, 0 -0.83em 0 -0.477em;
    }
    5%,
    95% {
      box-shadow: 0 -0.83em 0 -0.4em, 0 -0.83em 0 -0.42em, 0 -0.83em 0 -0.44em, 0 -0.83em 0 -0.46em, 0 -0.83em 0 -0.477em;
    }
    10%,
    59% {
      box-shadow: 0 -0.83em 0 -0.4em, -0.087em -0.825em 0 -0.42em, -0.173em -0.812em 0 -0.44em, -0.256em -0.789em 0 -0.46em, -0.297em -0.775em 0 -0.477em;
    }
    20% {
      box-shadow: 0 -0.83em 0 -0.4em, -0.338em -0.758em 0 -0.42em, -0.555em -0.617em 0 -0.44em, -0.671em -0.488em 0 -0.46em, -0.749em -0.34em 0 -0.477em;
    }
    38% {
      box-shadow: 0 -0.83em 0 -0.4em, -0.377em -0.74em 0 -0.42em, -0.645em -0.522em 0 -0.44em, -0.775em -0.297em 0 -0.46em, -0.82em -0.09em 0 -0.477em;
    }
    100% {
      -webkit-transform: rotate(360deg);
      transform: rotate(360deg);
      box-shadow: 0 -0.83em 0 -0.4em, 0 -0.83em 0 -0.42em, 0 -0.83em 0 -0.44em, 0 -0.83em 0 -0.46em, 0 -0.83em 0 -0.477em;
    }
  }

  @keyframes load6 {
    0% {
      -webkit-transform: rotate(0deg);
      transform: rotate(0deg);
      box-shadow: 0 -0.83em 0 -0.4em, 0 -0.83em 0 -0.42em, 0 -0.83em 0 -0.44em, 0 -0.83em 0 -0.46em, 0 -0.83em 0 -0.477em;
    }
    5%,
    95% {
      box-shadow: 0 -0.83em 0 -0.4em, 0 -0.83em 0 -0.42em, 0 -0.83em 0 -0.44em, 0 -0.83em 0 -0.46em, 0 -0.83em 0 -0.477em;
    }
    10%,
    59% {
      box-shadow: 0 -0.83em 0 -0.4em, -0.087em -0.825em 0 -0.42em, -0.173em -0.812em 0 -0.44em, -0.256em -0.789em 0 -0.46em, -0.297em -0.775em 0 -0.477em;
    }
    20% {
      box-shadow: 0 -0.83em 0 -0.4em, -0.338em -0.758em 0 -0.42em, -0.555em -0.617em 0 -0.44em, -0.671em -0.488em 0 -0.46em, -0.749em -0.34em 0 -0.477em;
    }
    38% {
      box-shadow: 0 -0.83em 0 -0.4em, -0.377em -0.74em 0 -0.42em, -0.645em -0.522em 0 -0.44em, -0.775em -0.297em 0 -0.46em, -0.82em -0.09em 0 -0.477em;
    }
    100% {
      -webkit-transform: rotate(360deg);
      transform: rotate(360deg);
      box-shadow: 0 -0.83em 0 -0.4em, 0 -0.83em 0 -0.42em, 0 -0.83em 0 -0.44em, 0 -0.83em 0 -0.46em, 0 -0.83em 0 -0.477em;
    }
  }

  @media (max-width: 481px) {
    .payment_box {
      padding: 50px 0;
    }

    .message-succsess {
      font-size: 16px;
    }
  }

  #order_errors {
    display: none;
    color: #cf0001;
  }

  .form {
    width: 100%;
  }
</style>