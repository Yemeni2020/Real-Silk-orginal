<?php

use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\MoneyRequestController;
use App\Http\Controllers\OTPController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VirtualCardController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\UserAuthController;
use App\Http\Controllers\API\RecipientController;
use App\Http\Controllers\API\MoneyTransferController;
use App\Http\Controllers\API\SupportTicketController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\DepositController;
use App\Http\Controllers\API\TwoFASecurityController;
use App\Http\Controllers\API\VerificationController;
use App\Http\Controllers\API\CardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('virtual-card/ufitpay/callback', [VirtualCardController::class, 'ufitpayCallBack'])->name('ufitpay.Callback');
Route::post('virtual-card/flutterwave/callback', [VirtualCardController::class, 'flutterwavedCallBack'])->name('flutterwave.Callback');
Route::post('payout/{code}', [VirtualCardController::class, 'payout'])->name('payout');


/*=== API For Application ===*/
Route::get('app-config', [HomeController::class, 'appConfig']);
Route::get('language/{id?}', [HomeController::class, 'language']);

/*-- User Authentication --*/
Route::post('register', [UserAuthController::class, 'register']);
Route::post('login', [UserAuthController::class, 'login']);
Route::post('recovery-pass/get-email', [UserAuthController::class, 'getEmailForRecoverPass']);
Route::post('recovery-pass/get-code', [UserAuthController::class, 'getCodeForRecoverPass']);
Route::post('update-pass', [UserAuthController::class, 'updatePass'])->middleware('throttle:3,1');
Route::post('logout', [UserAuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

    /*--Verification--*/
    Route::post('twoFA-Verify', [VerificationController::class, 'twoFAverify']);
    Route::post('mail-verify', [VerificationController::class, 'mailVerify']);
    Route::post('sms-verify', [VerificationController::class, 'smsVerify']);
    Route::get('resend-code', [VerificationController::class, 'resendCode']);

    Route::group(['middleware' => ['CheckVerificationApi']], function () {
        Route::get('transaction-list', [HomeController::class, 'transactionList']);
        Route::get('fund-list', [HomeController::class, 'fundList']);
        Route::get('referral-list', [HomeController::class, 'referList']);
        Route::get('referral-details', [HomeController::class, 'referList']);
        Route::get('gateways', [HomeController::class, 'gateways']);
        Route::get('notification-settings', [HomeController::class, 'notificationSettings']);
        Route::post('notification-permission', [HomeController::class, 'notificationPermissionStore']);
        Route::get('pusher-config', [HomeController::class, 'pusherConfig']);

        Route::get('dashboard', [HomeController::class, 'dashboard']);

        /*wallet*/
        Route::get('wallet-list', [WalletController::class, 'walletList']);
        Route::post('wallet-store', [WalletController::class, 'store']);
        Route::post('wallet-exchange', [WalletController::class, 'walletExchange']);
        Route::post('money-exchange', [WalletController::class, 'moneyExchange']);
        Route::get('wallet-transaction/{uuid}', [WalletController::class, 'walletTransaction']);
        Route::post('default-wallet/{id}', [WalletController::class, 'defaultWallet']);


        /*--profile--*/
        Route::get('profile', [ProfileController::class, 'profile']);
        Route::post('profile-update/image', [ProfileController::class, 'profileUpdateImage']);
        Route::post('profile-update', [ProfileController::class, 'profileUpdate']);
        Route::put('email-update/{user}', [ProfileController::class, 'updateEmail']);
        Route::post('update-password', [ProfileController::class, 'updatePassword']);
        Route::get('verify/{id?}', [ProfileController::class, 'verify']);
        Route::post('kyc-submit', [ProfileController::class, 'kycVerificationSubmit']);

        Route::post('/delete-account', [ProfileController::class, 'deleteAccount']);
        Route::post('/logout-from-all-devices', [HomeController::class, 'logoutFromAllDevices'])
            ->middleware(['auth', 'web']);

        /*--2FA Security--*/
        Route::get('2FA-security', [TwoFASecurityController::class, 'twoFASecurity']);
        Route::post('2FA-security/enable', [TwoFASecurityController::class, 'twoFASecurityEnable']);
        Route::post('2FA-security/disable', [TwoFASecurityController::class, 'twoFASecurityDisable']);

        /*--ticket--*/
        Route::get('ticket-list', [SupportTicketController::class, 'ticketList']);
        Route::get('ticket-view/{ticketId}', [SupportTicketController::class, 'ticketView']);
        Route::post('create-ticket', [SupportTicketController::class, 'createTicket']);
        Route::post('reply-ticket/{id}', [SupportTicketController::class, 'replyTicket']);
        Route::patch('close-ticket/{id}', [SupportTicketController::class, 'closeTicket']);
        Route::delete('delete-ticket/{ticketId}', [SupportTicketController::class, 'deleteTicket']);

        /*--recipient--*/
        Route::get('recipient-list', [RecipientController::class, 'recipientList']);
        Route::get('recipient-details/{uuid}', [RecipientController::class, 'recipientDetails']);
        Route::post('recipient-store', [RecipientController::class, 'store']);
        Route::post('recipient-user-store', [RecipientController::class, 'userStore']);
        Route::put('recipient-update-name/{recipient}', [RecipientController::class, 'updateName']);
        Route::delete('recipient-delete/{recipient}', [RecipientController::class, 'destroy']);

        Route::get('get-services', [RecipientController::class, 'getServices'])->name('getServices');
        Route::get('get-bank', [RecipientController::class, 'getBank'])->name('getBank');
        Route::get('generate-fields', [RecipientController::class, 'generateFields'])->name('generateFields');

        /* ===== Money Request ===== */
        Route::middleware('ApiKYC')->group(function () {
            Route::get('money-request-form/{uuid}', [MoneyRequestController::class, 'showRequestMoneyForm']);
            Route::post('money-request', [MoneyRequestController::class, 'requestMoney']);
            Route::post('money-request-action', [MoneyRequestController::class, 'moneyRequestAction'])->name('moneyRequestAction');
        });
        Route::get('money-request-list', [MoneyRequestController::class, 'moneyRequestList'])->name('moneyRequestList');
        Route::get('money-request-details/{trx_id}', [MoneyRequestController::class, 'moneyRequestDetails'])->name('moneyRequestDetails');

        /*--money transfer--*/
        Route::get('transfer-list', [MoneyTransferController::class, 'transferList']);
        Route::get('transfer-details/{uuid}', [MoneyTransferController::class, 'transferDetails']);
        Route::get('transfer-amount', [MoneyTransferController::class, 'transferAmount']);
        Route::match(['get', 'post'], '/transfer-recipient/{country?}', [MoneyTransferController::class, 'transferRecipient']);

        Route::middleware('ApiKYC')->group(function () {
            Route::get("transfer-review/{uuid}", [MoneyTransferController::class, 'transferReview']);
            Route::post("transfer-payment-store", [MoneyTransferController::class, 'paymentStore']);
        });

        Route::get("transfer-pay/{uuid}", [MoneyTransferController::class, 'transferPay']);
        Route::post("money-transfer-post", [MoneyTransferController::class, 'transferPayment']);
        Route::post("currency-rate", [MoneyTransferController::class, 'currencyRate']);

        Route::match(['get', 'post'], "transfer-otp", [OTPController::class, 'transferOtp']);

        /*--Payment--*/
        Route::get('supported-currency', [DepositController::class, 'supportedCurrency']);
        Route::get('deposit-check-amount', [DepositController::class, 'checkAmount']);
        Route::post('payment-request/{transfer?}', [DepositController::class, 'paymentRequest']);
        Route::get('payment-process/{trx_id}', [PaymentController::class, 'depositConfirm']);
        Route::post('addFundConfirm/{trx_id}', [PaymentController::class, 'fromSubmit']);

        Route::post('card-payment', [PaymentController::class, 'cardPayment']);
        Route::post('payment-done', [PaymentController::class, 'paymentDone']);
        Route::get('payment-webview', [PaymentController::class, 'paymentWebview']);

        /* Virtual Card */
        Route::get('virtual-cards', [CardController::class, 'index']);
        Route::get('virtual-card/order', [CardController::class, 'order']);
        Route::post('virtual-card/order/submit', [CardController::class, 'orderSubmit']);
        Route::match(['get', 'post'], 'virtual-card/confirm/{utr}', [CardController::class, 'confirmOrder']);
        Route::any('virtual-card/order/re-submit', [CardController::class, 'orderReSubmit']);

        Route::post('virtual-card/block/{id}', [CardController::class, 'cardBlock']);
        Route::get('virtual-card/transaction/{id?}', [CardController::class, 'cardTransaction']);


    });

});

