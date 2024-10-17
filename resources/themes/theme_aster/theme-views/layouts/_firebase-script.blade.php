@php($fcmCredentials = getWebConfig('fcm_credentials'))
<span id="Firebase_Configuration_Config" data-api-key="{{ $fcmCredentials['apiKey'] ?? '' }}"
      data-auth-domain="{{ $fcmCredentials['authDomain'] ?? '' }}"
      data-project-id="{{ $fcmCredentials['projectId'] ?? '' }}"
      data-storage-bucket="{{ $fcmCredentials['storageBucket'] ?? '' }}"
      data-messaging-sender-id="{{ $fcmCredentials['messagingSenderId'] ?? '' }}"
      data-app-id="{{ $fcmCredentials['appId'] ?? '' }}"
      data-measurement-id="{{ $fcmCredentials['measurementId'] ?? '' }}"
      data-csrf-token="{{ csrf_token() }}"
      data-recaptcha-store="{{ route('g-recaptcha-response-store') }}"
      data-firebase-service-worker-file="{{ dynamicAsset(path: 'firebase-messaging-sw.js') }}"
      data-firebase-service-worker-scope="{{ dynamicAsset(path: 'firebase-cloud-messaging-push-scope') }}"
>
</span>

<script src="{{ theme_asset(path: 'assets/plugins/firebase/firebase.min.js') }}"></script>
<script src="{{ 'https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js' }}"></script>
<script src="{{ 'https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js' }}"></script>
<script>
    try {
        let firebaseConfigurationConfig = $('#Firebase_Configuration_Config');
        var firebaseConfig = {
            apiKey: firebaseConfigurationConfig.data('api-key'),
            authDomain: firebaseConfigurationConfig.data('auth-domain'),
            projectId: firebaseConfigurationConfig.data('project-id'),
            storageBucket: firebaseConfigurationConfig.data('storage-bucket'),
            messagingSenderId: firebaseConfigurationConfig.data('messaging-sender-id'),
            appId: firebaseConfigurationConfig.data('app-id'),
            measurementId: firebaseConfigurationConfig.data('measurement-id'),
        };
        firebase.initializeApp(firebaseConfig);

        var recaptchaVerifiers = {};

        window.onload = function() {
            initializeFirebaseGoogleRecaptcha('recaptcha-container-otp', 'OTP Verification');
            initializeFirebaseGoogleRecaptcha('recaptcha-container-manual-login', 'Manual Login');
            initializeFirebaseGoogleRecaptcha('recaptcha-container-verify-token', 'Token Verification');
        };

        function initializeFirebaseGoogleRecaptcha(containerId, action) {
            try {
                var recaptchaContainer = document.getElementById(containerId);

                if (recaptchaVerifiers[containerId]) {
                    recaptchaVerifiers[containerId].clear();
                }

                if (recaptchaContainer && recaptchaContainer.innerHTML.trim() === "") {
                    recaptchaVerifiers[containerId] = new firebase.auth.RecaptchaVerifier(containerId, {
                        size: 'normal',  // Use 'invisible' for invisible reCAPTCHA
                        callback: function(response) {
                            console.log('reCAPTCHA solved for ' + containerId + ' with action ' + action);
                            storeRecaptchaVerifierResponse(containerId, response);
                        },
                        'expired-callback': function() {
                            console.error('reCAPTCHA expired for ' + containerId);
                        }
                    });

                    recaptchaVerifiers[containerId].render().then(function(widgetId) {
                        console.log('reCAPTCHA widget rendered for ' + containerId);
                    }).catch(function(error) {
                        console.error('Error rendering reCAPTCHA for ' + containerId + ':', error);
                    });
                } else {
                    console.log("reCAPTCHA container " + containerId + " is either not found or already contains inner elements!");
                }
            } catch (e) {
                console.log(e)
            }
        }

        function storeRecaptchaVerifierResponse(containerId, response) {
            console.log('Response from ' + containerId + ': ' + response);
        }
    } catch (e) {
        console.log(e);
    }
</script>
