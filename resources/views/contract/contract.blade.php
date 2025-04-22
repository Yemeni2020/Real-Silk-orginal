<?php
    use Illuminate\Support\Facades\Session;
    $direction = empty($lang)?"rtl":request()->cookie('direction', 'ltr');
    $lang=empty($lang)?"ar":$lang;
    $currencyCode = getCurrencyCode(type: 'default');
    $lang = getDefaultLanguage();
    if(isset($vendor)){

        $latestSignature = $vendor->signatures()->latest()->first();

        $signaturePath = $latestSignature?->signature_path??'';
        $date_signature = $latestSignature?->created_at?->format('Y-m-d H:i');
        // dump($vendor->f_name);
        $fullname=!empty($fullname)?$fullname:($vendor?->f_name ?? "")." ".($vendor?->l_name??"");
        $shopName = $vendor?->shop?->name??"";
        $number_cr = $vendor?->shop?->number_cr??"";
        $country = $vendor?->shop?->country??"";
        $city = $vendor?->shop?->city??"";
        $address = $vendor?->shop?->address??"";
        $signature_img=!empty($signaturePath)?"<img src='$signaturePath' style='width: 100px; height:100px;' >":"";
    }else{
        $fullname=$fullname??"";
        $shopName =$shopName??"";
        $number_cr=$number_cr??"";
        $country=$country??"";
        $city=$city??"";
        $address=$address??"";
        $date_signature=date('Y-m-d H:i');
    }
    $placeholders = [
        '{{@full_name}}' => $fullname ?? '',
        '{{@shopName}}' => $shopName ?? '',
        '{{@signature}}' => $signature_img ?? '',
        '{{@date_signature}}' => $date_signature ?? '',
        '{{@number_cr}}' => $number_cr ?? '',
        '{{@country}}' => $country ?? '',
        '{{@city}}' => $city ?? '',
        '{{@address}}' => $address ?? '',
    ];
    
    foreach ($placeholders as $key => $value) {
        $contract = str_replace($key, $value, $contract);
    }

?>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{$direction}}"
      style="text-align: {{$direction === "rtl" ? 'right' : 'left'}};"
      xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('invoice')}}</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
            src: url({{dynamicAsset('public/assets/front-end/fonts/Inter/UcC73FwrK3iLTeHuS_fvQtMwCp50KnMa2JL7SUc.woff2')}}) format('woff2');
            unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
        }

        /* cyrillic */
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
            src: url({{dynamicAsset('public/assets/front-end/fonts/Inter/UcC73FwrK3iLTeHuS_fvQtMwCp50KnMa0ZL7SUc.woff')}}) format('woff2');
            unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
        }

        /* greek-ext */
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
            src: url({{dynamicAsset('public/assets/front-end/fonts/Inter/UcC73FwrK3iLTeHuS_fvQtMwCp50KnMa2ZL7SUc.woff')}}) format('woff2');
            unicode-range: U+1F00-1FFF;
        }

        /* greek */
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
            src: url({{dynamicAsset('public/assets/front-end/fonts/Inter/UcC73FwrK3iLTeHuS_fvQtMwCp50KnMa1pL7SUc.woff')}}) format('woff2');
            unicode-range: U+0370-0377, U+037A-037F, U+0384-038A, U+038C, U+038E-03A1, U+03A3-03FF;
        }

        /* vietnamese */
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
            src: url({{dynamicAsset('public/assets/front-end/fonts/Inter/UcC73FwrK3iLTeHuS_fvQtMwCp50KnMa2pL7SUc.woff')}}) format('woff2');
            unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
        }

        /* latin-ext */
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
            src: url({{dynamicAsset('public/assets/front-end/fonts/Inter/UcC73FwrK3iLTeHuS_fvQtMwCp50KnMa25L7SUc.woff')}}) format('woff2');
            unicode-range: U+0100-02AF, U+0304, U+0308, U+0329, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
        }

        /* latin */
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 100 900;
            font-display: swap;
            src: url({{dynamicAsset('public/assets/front-end/fonts/Inter/UcC73FwrK3iLTeHuS_fvQtMwCp50KnMa1ZL7.woff')}}) format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        * {
            margin: 0;
            padding: 0;
            line-height: 1.6;
            font-family: "Inter", sans-serif;
            color: #6A707C;
        }

        .ltr {
            direction: ltr;
        }

        .rtl {
            direction: rtl;
        }

        body {
            font-size: .75rem;
            font-family: "Inter", sans-serif;
            font-optical-sizing: auto;
            font-weight: < weight >;
            font-style: normal;
            font-variation-settings: "slnt" 0;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #f1f1f1;
            text-align: center;
            padding: 10px;
        }

        img {
            max-width: 100%;
        }

        .customers {
            border-collapse: collapse;
            width: 100%;
        }

        table {
            width: 100%;
        }

        table thead th {
            padding: 8px;
            font-size: 9px;
        }

        table tbody th,
        table tbody td {
            padding: 8px;
            color: #6A707C;
        }

        table.fz-12 thead th {
            font-size: 12px;
        }

        table.fz-12 tbody th,
        table.fz-12 tbody td {
            font-size: 12px;
        }

        table.fz-10 thead th {
            font-size: 10px;
        }

        table.fz-10 tbody th,
        table.fz-10 tbody td {
            font-size: 10px;
        }

        table.customers thead th {
            background-color: #F5FBFF;
            color: #222222;
            border-top: 1px solid #D6EBFF;
            border-bottom: 1px solid #D6EBFF;
            padding-top: 10px;
        }

        table.customers tbody th{
            background-color: #FAFCFF;
        }

        table.customers tbody td {
            padding-block: 10px;
            border-bottom: 1px solid #D7DAE0;
        }

        .calc-table * {
            color: #222222
        }

        .calc-table td {
            padding-inline: 0 !important
        }

        .calc-table {
            padding: 0 !important
        }

        .text-left {
            text-align: {{$direction === "rtl" ? 'right' : 'left'}}  !important;
        }

        .pb-2 {
            padding-bottom: 8px !important;
        }

        .pb-3 {
            padding-bottom: 16px !important;
        }

        .text-right {
            text-align: {{$direction === "rtl" ? 'left' : 'right'}}  !important;
        }

        table th.text-right {
            text-align: {{$direction === "rtl" ? 'left' : 'right'}}  !important;
        }

        @media print {
            table th.text-right {
                text-align: {{$direction === "rtl" ? 'left' : 'right'}}  !important;
            }
        }

        .content-position {
            padding: 30px 20px 10px;
        }

        .content-position-y {
            padding: 0 40px;
        }

        .text-white {
            color: white !important;
        }

        .bs-0 {
            border-spacing: 0;
        }


        .mb-1 {
            margin-bottom: 4px !important;
        }

        .mb-2 {
            margin-bottom: 8px !important;
        }

        .mb-4 {
            margin-bottom: 24px !important;
        }

        .mb-30 {
            margin-bottom: 30px !important;
        }

        .px-10 {
            padding-inline-start: 10px;
            padding-inline-end: 10px;
        }

        .fz-14 {
            font-size: 14px;
        }

        .fz-12 {
            font-size: 12px;
        }

        .fz-10 {
            font-size: 10px;
        }

        .font-normal {
            font-weight: 400;
        }

        .font-weight-normal {
            font-weight: normal;
        }

        .border-dashed-top {
            border-top: 1px dashed #ddd;
        }

        .font-weight-bold {
            font-weight: 700;
        }

        .bg-light {
            background-color: #F7F7F7;
        }

        .py-30 {
            padding-top: 30px;
            padding-bottom: 30px;
        }

        .py-4 {
            padding-top: 24px;
            padding-bottom: 24px;
        }

        .d-flex {
            display: flex;
            gap: 3px;
        }

        .align-items-center {
            align-items: center;
        }

        .gap-2 {
            gap: 8px;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .align-items-center {
            align-items: center;
        }

        .justify-content-center {
            justify-content: center;
        }

        a {
            color: rgba(0, 128, 245, 1);
        }

        .p-1 {
            padding: 4px !important;
        }

        .h2 {
            font-size: 1.5em;
            margin-block-start: 0.83em;
            margin-block-end: 0.83em;
            margin-inline-start: 0;
            margin-inline-end: 0;
            font-weight: bold;
            color: #222222;
        }

        .h4 {
            margin-block-start: 1.33em;
            margin-block-end: 1.33em;
            margin-inline-start: 0;
            margin-inline-end: 0;
            font-weight: bold;
            color: #222222;
        }

        .m-0 {
            margin: 0;
        }

        .my-0 {
            margin-top: 0;
            margin-bottom: 0;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mt-6px {
            margin-top: 6px;
        }

        .font-size-26px {
            font-size: 26px
        }

        .w-100 {
            width: 100%;
        }

        .width-60 {
            width: 60%;
        }

        .fz-17 {
            font-size: 17px;
        }

        .text-primary {
            color: #0177CD;
        }

        .border {
            border: 1px solid #D7DAE0;
        }

        .border-bottom {
            border-bottom: 1px solid #D7DAE0;
        }

        .border-left {
            border-left: 1px solid #D7DAE0;
        }

        .font-bold {
            font-weight: {{$lang == 'bd' ?'700':'bold' }};
            color: #222222;
        }

        .vertical-align-top {
            vertical-align: top;
        }

        .font-semibold {
            font-weight: 600;
            color: #222222;
        }

        .fz-11 {
            font-size: 11px;
        }

        .fz-14 {
            font-size: 14px !important;
        }

        .h-100 {
            height: 100%;
        }

        .font-medium {
            font-weight: 600;
            color: #222222;
        }

        .text-capitalize {
            text-transform: capitalize;
        }

        .text-dark, strong {
            color: #222222;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .pt-0 {
            padding-top: 0 !important;
        }

        .pb-0 {
            padding-bottom: 0 !important;
        }

    </style>
</head>

<body>
    
    <div class="first content-position" style="width:595px;margin: 0 auto; margin-bottom:140px;">
        <?php
        echo $contract;
        ?>
    </div>
    <div class="footer">
        <div class="row">
                <table>
                    <tr>
                        <td><h4>الطرف الاول:</h4> </td>
                        <td><h4>شركة حرير حقيقي التجارية</h4></td>
                    </tr>
                    <tr>
                        <td><h4>الطرف الثاني:</h4> </td>
                        <td>
                            <h4>{{$fullname??''}}</h4>
                            @if(isset($vendor))
                                {!!$signature_img!!}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
