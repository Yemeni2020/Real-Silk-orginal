<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <!-- resources/views/emails/contract.blade.php -->
    <p>السلام عليكم،</p>
    <p>تم توليد عقد جديد لشركة: <strong>{{ $vendor->shop->name }}</strong></p>
    <p>مرفق نسخة PDF من العقد في هذه الرسالة.</p>
    <p>شكراً لكم،</p>
    <p>فريق حرير حقيقي</p>

</body>
</html>