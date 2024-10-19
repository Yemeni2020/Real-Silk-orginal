<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="{{ asset('public/assets/installation/assets/css/bootstrap.min.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('public/css/card.css') }}">

    <title>Document</title>
</head>
<body>
    <link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400&display=swap" rel="stylesheet">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<div class="box">

  
  <div class="mdl">
  <div class="circles">
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>
  </div>  
      <h1 class="title">
        Please enter your credit card <br>details below
      </h1>
    
    <div class="card">

      <form action="{{route('GoTapPayment')}}" method="post">
      @csrf
        <div class="logo">
          <?xml version="1.0" encoding="UTF-8"?>
          <svg width="48px" height="48px" viewBox="0 0 64 64" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <title>Group</title>
            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
              <g id="Group" fill="#FFFFFF">
                <circle id="Oval" cx="16" cy="16" r="16"></circle>
                <circle id="Oval" cx="16" cy="48" r="16"></circle>
                <circle id="Oval" cx="48" cy="16" r="16"></circle>
                <circle id="Oval" cx="48" cy="48" r="16"></circle>
              </g>
            </g>
          </svg>

        </div>
        <div class="card-number">
          <label>Card Number</label>
          <input id="card-number" placeholder="1234 1234 1234 1234" autofocus type="text" name="number_card" required maxlength="19">
          <span class="underline"></span>

        </div>
        <br>
        <div class="group">
          <div class="card-name">
            <label>Card Holder</label>
            <input id="card-name" placeholder="Esmail Haimi" value="{{$first_name}} {{$last_name}}" name="name" type="text" required>
            <span class="underline"></span>

          </div>
          <div class="expiration-date">
            <label>Exp. Date</label>
            <input id="card-exp" placeholder="10/25" type="text" maxlength="5" name="dte" required>
            <span class="underline"></span>

          </div>
          <div class="ccv">
            <label>CCV</label>
            <input id="card-ccv" placeholder="123" type="text" name="ccv" maxlength="3" required>
            <span class="underline"></span>
          </div>
          
        </div>
        <input type="hidden" name="first_name" value="{{$first_name}}">
        <input type="hidden" name="last_name" value="{{$last_name}}">
        <input type="hidden" name="email" value="{{$email}}">
        <input type="hidden" name="amount" value="{{$amount}}">
        <input type="hidden" name="phone" value="{{$phone}}">
        <input type="hidden" name="country_code" value="{{$country_code}}">
        <input type="text" readonly name="currency_code" value="{{session('currency_code')}}">
        
        <button class="btn btn-primary" type="submit" class="btn btn-primary">Payment</button>
        <button  onclick="window.history.back();"  type="button">cancel</button>
      </form>
    </div>
  </div>

</div>
<script src="{{ asset('public/js/card.js') }}"></script>
</body>
</html>