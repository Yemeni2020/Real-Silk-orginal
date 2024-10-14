<?php

namespace App\Http\Controllers\factory\Auth;

use App\Contracts\Repositories\factoryRepositoryInterface;
use App\Enums\SessionKey;
use App\Enums\ViewPaths\factory\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\factory\LoginRequest;
use App\Repositories\factoryWalletRepository;
use App\Services\factoryService;
use App\Traits\RecaptchaTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use RecaptchaTrait;

    public function __construct(
        private readonly factoryRepositoryInterface $factoryRepo,
        private readonly factoryService             $factoryService,
        private readonly factoryWalletRepository    $factoryWalletRepository,

    )
    {
        $this->middleware('guest:factory', ['except' => ['logout']]);
    }

    public function generateReCaptcha(): void
    {
        $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
        if (Session::has(SessionKey::factory_RECAPTCHA_KEY)) {
            Session::forget(SessionKey::factory_RECAPTCHA_KEY);
        }
        Session::put(SessionKey::factory_RECAPTCHA_KEY, $recaptchaBuilder->getPhrase());
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $recaptchaBuilder->output();
    }

    public function getLoginView(): View
    {
        $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
        $recaptcha = getWebConfig(name: 'recaptcha');
        Session::put(SessionKey::factory_RECAPTCHA_KEY, $recaptchaBuilder->getPhrase());
        return view(Auth::factory_LOGIN[VIEW], compact('recaptchaBuilder', 'recaptcha'));
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $recaptcha = getWebConfig(name: 'recaptcha');

        if (isset($recaptcha) && $recaptcha['status'] == 1) {
            $request->validate([
                'g-recaptcha-response' => [
                    function ($attribute, $value, $fail) {
                        $secret_key = getWebConfig(name: 'recaptcha')['secret_key'];
                        $response = $value;
                        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response;
                        $response = Http::get($url);
                        $response = $response->json();
                        if (!isset($response['success']) || !$response['success']) {
                            $fail(translate('recaptcha_failed'));
                        }
                    },
                ],
            ]);
        } else {
            // echo $request->factoryRecaptchaKey;
            // return  1;
            if ($recaptcha['status'] != 1 && strtolower($request->factoryRecaptchaKey) != strtolower(Session(SessionKey::factory_RECAPTCHA_KEY))) {
                Session::forget(SessionKey::factory_RECAPTCHA_KEY);
                return back()->withErrors(translate('captcha_failed'));
            }
        }

        $factory = $this->factoryRepo->getFirstWhere(['identity' => $request['email']]);
        if (isset($factory) && $factory['status'] !== 'approved') {
            $statusMessages = [
                'pending' => translate('your_account_is_not_approved_yet') . '.',
                'suspended' => translate('your_account_has_been_suspended') . '!'
            ];
            return redirect()->back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([$statusMessages[$factory->status]]);
        }

        if ($this->factoryService->isLoginSuccessful($request->email, $request->password, $request->remember)) {
            if ($this->factoryWalletRepository->getFirstWhere(params:['id'=>auth('factory')->id()]) === false) {
                $this->factoryWalletRepository->add($this->factoryService->getInitialWalletData());
            }
            Toastr::info(translate('welcome_to_your_dashboard') . '!');
            return redirect()->route('factory.dashboard.index');
        }

        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([translate('credentials_do_not_match_or_your_account_has_been_suspended')]);
    }

    public function logout(): RedirectResponse
    {
        $this->factoryService->logout();
        session()->flash('success', translate('logged_out_successfully'));
        return redirect()->route('factory.auth.login');
    }
}
