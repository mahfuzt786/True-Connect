<?php

class SocialAuthService {

    public function getGoogleUser(string $code): array {
        $cfg = config('platforms');
        // Exchange code for token
        $tokenRes = $this->httpPost('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => $cfg['google_client_id'],
            'client_secret' => $cfg['google_client_secret'],
            'redirect_uri'  => url('/auth/google/callback'),
            'grant_type'    => 'authorization_code',
        ]);
        if (empty($tokenRes['access_token'])) throw new RuntimeException('Failed to get Google access token');
        $userRes = $this->httpGet('https://www.googleapis.com/oauth2/v3/userinfo', $tokenRes['access_token']);
        return [
            'id'    => $userRes['sub'],
            'email' => $userRes['email'],
            'name'  => $userRes['name'] ?? '',
            'photo' => $userRes['picture'] ?? '',
        ];
    }

    public function getFacebookUser(string $code): array {
        $cfg = config('platforms');
        $tokenRes = $this->httpGet("https://graph.facebook.com/v18.0/oauth/access_token?" . http_build_query([
            'client_id'     => $cfg['facebook_app_id'],
            'client_secret' => $cfg['facebook_app_secret'],
            'redirect_uri'  => url('/auth/facebook/callback'),
            'code'          => $code,
        ]));
        if (empty($tokenRes['access_token'])) throw new RuntimeException('Failed to get Facebook access token');
        $userRes = $this->httpGet("https://graph.facebook.com/me?fields=id,name,email,picture&access_token={$tokenRes['access_token']}");
        return [
            'id'    => $userRes['id'],
            'email' => $userRes['email'] ?? "{$userRes['id']}@facebook.local",
            'name'  => $userRes['name'] ?? '',
            'photo' => $userRes['picture']['data']['url'] ?? '',
        ];
    }

    public function findOrCreateUser(array $socialUser, string $provider): array {
        $providerId  = $socialUser['id'];
        $email       = $socialUser['email'];
        $providerCol = $provider . '_id';

        $user = Database::fetch("SELECT * FROM users WHERE $providerCol = ? OR email = ?", [$providerId, $email]);
        if ($user) {
            if (!$user[$providerCol]) {
                Database::update('users', [$providerCol => $providerId], 'id = ?', [$user['id']]);
            }
            return $user;
        }

        $userId = Database::insert('users', [
            'name'              => $socialUser['name'],
            'email'             => $email,
            'password'          => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
            'avatar'            => $socialUser['photo'] ?? null,
            $providerCol        => $providerId,
            'role'              => 'customer',
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
        return Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    }

    private function httpPost(string $url, array $data): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?? [];
    }

    private function httpGet(string $url, ?string $token = null): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $token ? ['Authorization: Bearer ' . $token] : [],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?? [];
    }
}
