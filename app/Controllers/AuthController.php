<?php

class AuthController extends Controller {

    public function showRegister(): void {
        if (Auth::check()) redirect('/dashboard');
        $this->view('auth.register', ['title' => 'Sign up'], 'auth');
    }

    public function register(): void {
        CSRF::validateOrFail();
        if (!RateLimit::forIp($this->request->ip(), 'register', 5, 300)) {
            $this->error('Too many registration attempts. Please try again later.');
            return;
        }
        $data = $this->validate([
            'name'                  => 'required|min:2|max:100',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|password_strength|confirmed',
            'password_confirmation' => 'required',
            'terms'                 => 'required',
        ]);

        try {
            // Plain buyer signup — role defaults to 'customer'.
            $userId = Auth::register($data);
            $user   = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);

            try { (new EmailService())->sendEmailVerification($user); } catch (Throwable) {}

            Session::flash('success', 'Account created! Please check your email to verify your account.');
            redirect('/login');
        } catch (Throwable $e) {
            logError('Registration error: ' . $e->getMessage());
            $this->error('Registration failed. Please try again.');
        }
    }

    /**
     * Seller signup. Two flavors selected by the user:
     *   - ecommerce  → they will own a single-vendor store (store_owner)
     *   - marketplace → they will join a multi-vendor marketplace (vendor)
     */
    public function showSellerRegister(): void {
        if (Auth::check()) redirect('/dashboard');
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 AND slug <> 'custom' ORDER BY sort_order");
        $this->view('auth.seller-register', ['title' => 'Become a seller', 'plans' => $plans], 'auth');
    }

    public function sellerRegister(): void {
        CSRF::validateOrFail();
        if (!RateLimit::forIp($this->request->ip(), 'register', 5, 300)) {
            $this->error('Too many registration attempts. Please try again later.');
            return;
        }

        $sellerType = $this->request->post('seller_type', '');
        if (!in_array($sellerType, ['ecommerce', 'marketplace'], true)) {
            $this->error('Please choose how you want to sell.');
            return;
        }

        $rules = [
            'name'                  => 'required|min:2|max:100',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|password_strength|confirmed',
            'password_confirmation' => 'required',
            'seller_type'           => 'required|in:ecommerce,marketplace',
            'terms'                 => 'required',
        ];
        if ($sellerType === 'ecommerce') {
            $rules['plan_id'] = 'required|integer|exists:plans,id';
        }
        $data = $this->validate($rules);

        try {
            $userId = Auth::register($data);
            $user   = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);

            try { (new EmailService())->sendEmailVerification($user); } catch (Throwable) {}

            // Carry the seller intent into the post-login wizard.
            Session::set('intent_seller_type', $sellerType);
            if ($sellerType === 'ecommerce' && !empty($data['plan_id'])) {
                Session::set('intent_store_type', 'ecommerce');
                Session::set('intent_plan_id',   (int)$data['plan_id']);
            } else {
                // Marketplace vendors don't pick a plan; their store does.
                Session::set('intent_store_type', 'marketplace');
            }

            $where = $sellerType === 'ecommerce' ? 'set up your store' : 'apply to a marketplace';
            Session::flash('success', "Account created! Sign in to $where.");
            redirect('/login');
        } catch (Throwable $e) {
            logError('Seller registration error: ' . $e->getMessage());
            $this->error('Registration failed. Please try again.');
        }
    }

    public function showLogin(): void {
        if (Auth::check()) redirect('/dashboard');
        $this->view('auth.login', ['title' => 'Login'], 'auth');
    }

    public function login(): void {
        CSRF::validateOrFail();
        $ip = $this->request->ip();

        if (!RateLimit::forIp($ip, 'login', 10, 900)) {
            $this->error('Too many login attempts. Please try again in 15 minutes.');
            return;
        }

        $email    = $this->request->post('email', '');
        $password = $this->request->post('password', '');
        $remember = (bool)$this->request->post('remember', false);

        if (!$email || !$password) {
            $this->error('Email and password are required.');
            return;
        }

        // 2FA check
        $user = Database::fetch("SELECT * FROM users WHERE email = ? AND status = 'active'", [$email]);
        if ($user && $user['two_factor_enabled'] && $user['email_verified_at']) {
            if (!password_verify($password, $user['password'])) {
                $this->error('Invalid credentials.');
                return;
            }
            Session::set('2fa_pending_user_id', $user['id']);
            Session::set('2fa_remember', $remember);
            redirect('/login/2fa');
            return;
        }

        if (Auth::attempt($email, $password, $remember)) {
            RateLimit::clear("login:$ip");
            $intended = Session::getFlash('intended', '/dashboard');
            redirect($intended);
        } else {
            $this->error('Invalid email or password.');
        }
    }

    public function logout(): void {
        CSRF::validateOrFail();
        Auth::logout();
        Session::flash('success', 'You have been logged out.');
        redirect('/login');
    }

    public function verifyEmail(string $token): void {
        if (Auth::verifyEmail($token)) {
            Session::flash('success', 'Email verified! You can now log in.');
        } else {
            Session::flash('error', 'Invalid or expired verification link.');
        }
        redirect('/login');
    }

    public function resendVerification(): void {
        CSRF::validateOrFail();
        $email = $this->request->post('email', '');
        $user  = Database::fetch("SELECT * FROM users WHERE email = ? AND email_verified_at IS NULL", [$email]);
        if ($user) {
            (new EmailService())->sendEmailVerification($user);
        }
        Session::flash('success', 'If that email exists, a verification link has been sent.');
        redirect('/login');
    }

    public function showForgotPassword(): void {
        $this->view('auth.forgot-password', ['title' => 'Forgot Password'], 'auth');
    }

    public function sendResetLink(): void {
        CSRF::validateOrFail();
        if (!RateLimit::forIp($this->request->ip(), 'password-reset', 3, 600)) {
            $this->error('Too many attempts. Please try again later.');
            return;
        }
        $email = $this->request->post('email', '');
        $token = Auth::createPasswordReset($email);
        if ($token) {
            $user = Database::fetch("SELECT * FROM users WHERE email = ?", [$email]);
            (new EmailService())->sendPasswordReset($user, $token);
        }
        Session::flash('success', 'If that email exists, a password reset link has been sent.');
        redirect('/forgot-password');
    }

    public function showResetPassword(string $token): void {
        $hash  = hash('sha256', $token);
        $reset = Database::fetch("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used_at IS NULL", [$hash]);
        if (!$reset) {
            Session::flash('error', 'Invalid or expired reset link.');
            redirect('/forgot-password');
            return;
        }
        $this->view('auth.reset-password', ['title' => 'Reset Password', 'token' => $token], 'auth');
    }

    public function resetPassword(): void {
        CSRF::validateOrFail();
        $data = $this->validate([
            'token'                 => 'required',
            'password'              => 'required|password_strength|confirmed',
            'password_confirmation' => 'required',
        ]);
        if (Auth::resetPassword($data['token'], $data['password'])) {
            Session::flash('success', 'Password reset successfully. Please log in.');
            redirect('/login');
        } else {
            $this->error('Invalid or expired reset link.');
        }
    }

    public function googleRedirect(): void {
        $cfg = config('platforms');
        $params = http_build_query([
            'client_id'     => $cfg['google_client_id'],
            'redirect_uri'  => url('/auth/google/callback'),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'online',
        ]);
        redirect('https://accounts.google.com/o/oauth2/auth?' . $params);
    }

    public function googleCallback(): void {
        $code = $this->request->get('code');
        if (!$code) {
            $this->error('Google authentication failed.');
            redirect('/login');
            return;
        }
        try {
            $socialService = new SocialAuthService();
            $googleUser    = $socialService->getGoogleUser($code);
            $user          = $socialService->findOrCreateUser($googleUser, 'google');
            Auth::login($user, true);
            redirect('/dashboard');
        } catch (Throwable $e) {
            logError('Google auth error: ' . $e->getMessage());
            $this->error('Google login failed. Please try again.');
            redirect('/login');
        }
    }

    public function facebookRedirect(): void {
        $cfg = config('platforms');
        $params = http_build_query([
            'client_id'     => $cfg['facebook_app_id'],
            'redirect_uri'  => url('/auth/facebook/callback'),
            'response_type' => 'code',
            'scope'         => 'email,public_profile',
        ]);
        redirect('https://www.facebook.com/v18.0/dialog/oauth?' . $params);
    }

    public function facebookCallback(): void {
        $code = $this->request->get('code');
        if (!$code) {
            $this->error('Facebook authentication failed.');
            redirect('/login');
            return;
        }
        try {
            $socialService = new SocialAuthService();
            $fbUser        = $socialService->getFacebookUser($code);
            $user          = $socialService->findOrCreateUser($fbUser, 'facebook');
            Auth::login($user, true);
            redirect('/dashboard');
        } catch (Throwable $e) {
            logError('Facebook auth error: ' . $e->getMessage());
            $this->error('Facebook login failed. Please try again.');
            redirect('/login');
        }
    }
}
