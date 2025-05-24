<?php
namespace App\Service;

use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JwtManager {
    private string $privateKeyPath;
    private string $publicKeyPath;
    private string $passphrase;

    public function __construct(string $privateKeyPath, string $publicKeyPath, string $passphrase) {
        $this->privateKeyPath = $privateKeyPath;
        $this->publicKeyPath = $publicKeyPath;
        $this->passphrase = $passphrase;
    }

    public function create(User $user): string {
        $privateKey = file_get_contents($this->privateKeyPath);

        $payload = [
            'username' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    public function decode(string $jwt) {
        $publicKey = file_get_contents($this->publicKeyPath);

        try {
            $headers = ['RS256'];
            return JWT::decode($jwt, $publicKey, $headers);
        } catch (ExpiredException $e) {
            throw new \Exception('Token expired');
        } catch (\Exception $e) {
            throw new \Exception('Invalid token');
        }
    }
}
