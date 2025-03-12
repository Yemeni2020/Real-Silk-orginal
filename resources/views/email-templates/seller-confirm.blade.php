<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('code_confirm') }}</title>
</head>
<body>
    <h2>{{ translate('Verify_your_email') }}</h2>
    <p style="font-size: 20px; font-weight: bold;">{{ $data["token"] }}</p>
    <p>{{ translate('Insert_your_code_in_website:') }}</p>
</body>
</html>
