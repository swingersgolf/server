@component('mail::message')
    # Swingers Email Verification

    <img src="{{ asset('images/logo.png') }}" alt="Swingers Logo" width="150">

    You are receiving this email because we received an account registration for this email.

    Please verify you own this email by entering the following code in your Swingers mobile application:

    # **{{ $code }}**

    This code will expire in **{{ $expiration }} minutes**.

    If you did not request the creation of this Swingers account, no further action is required.

    Thanks,
    **Swingers Team**

    @component('mail::button', ['url' => config('app.url')])
        Visit Our Website
    @endcomponent

@endcomponent
