<?php
/*
 * Copyright (C) 2026 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see
 *
 * <http://www.gnu.org/licenses/>.
 */

namespace PDR\Security;

class JwtHandler {
    private const JWT_ALGORITHM = 'HS256';
    private const HASH_ALGORITHM = 'sha256';
    private const EXPIRES_IN = 3600; // 1 hour

    public function generateAccessToken(\user $user): string {
        $payload = [
            'userPrimaryKey' => $user->get_primary_key(),
            'userName' => $user->get_user_name(),
            'iat' => time(), // Issued at time
            'exp' => time() + self::EXPIRES_IN, // Token expiration time (e.g., 1 hour)
        ];

        /**
         *  Encode the payload and sign it with a secret key
         */
        $token = $this->jwtEncode($payload);

        return $token;
    }

    public function verifyAccessToken(): ?\user {
        $token = $this->getTokenPayloadFromRequest();
        try {
            /**
             * Method to decode and verify the token with the secret key
             */
            $decodedToken = $this->jwtDecode($token);
            $this->validateToken($decodedToken); // This will exit if the token is invalid or expired
            return $this->getUserFromToken($decodedToken);
        } catch (\Throwable $exception) {
            $this->sendErrorAndExit('Invalid Token', 401);
        }
        return null; // This line will never be reached due to exit in sendErrorAndExit, but is here for clarity
    }

    private function getTokenPayloadFromRequest(): string {
        $token = "";
        $headers = getallheaders();

        /**
         * Test if Authorization-Header exists
         */
        if (!isset($headers['Authorization']) || empty($headers['Authorization'])) {
            $this->sendErrorAndExit('Authorization token missing', 401);
        }
        $authorizationHeader = $headers["Authorization"];
        if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            $token = $matches[1];
        } else {
            $this->sendErrorAndExit('Authorization without Bearer token', 401);
        }

        return $token;
    }

    private function validateToken(array $decodedToken): void {
        /**
         * Check token expiration
         */
        if (($decodedToken['exp'] ?? 0) < time()) {
            $this->sendErrorAndExit('Token expired', 401);
        }
        if (empty($decodedToken['userPrimaryKey'] ?? null)) {
            $this->sendErrorAndExit('Invalid token payload', 401);
        }
    }

    private function getUserFromToken(array $decodedToken): \user {
        $userId = (int) $decodedToken['userPrimaryKey'];

        /**
         * Hydrate a user object for the duration of this request
         */
        $userObj = new \user($userId);
        if (!($userObj instanceof \user) || !$userObj->exists()) {
            $this->sendErrorAndExit('User not found', 401);
        }

        /**
         * Populate expected session slot so existing code works
         */
        $_SESSION['user_object'] = $userObj;
        return $userObj;
    }

    private function jwtEncode(array $payload): string {

        $header = ['alg' => self::JWT_ALGORITHM, 'typ' => 'JWT'];
        $jsonHeader = json_encode($header);
        $jsonPayload = json_encode($payload);
        $encodedHeader = $this->base64url_encode($jsonHeader);
        $encodedPayload = $this->base64url_encode($jsonPayload);
        /**
         * Signature
         */
        $configuration = new \PDR\Application\Configuration();
        $secretKey = $configuration->getSecretKey();
        $signature = hash_hmac(
            self::HASH_ALGORITHM,
            $encodedHeader . '.' . $encodedPayload,
            $secretKey,
            true
        );
        $encodedSignature = $this->base64url_encode($signature);
        /**
         * Token creation
         */
        $token = $encodedHeader . '.' . $encodedPayload . '.' . $encodedSignature;
        return $token;
    }

    private function jwtDecode(string $token): array {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \Exception('Malformed token');
        }

        [$header, $payload, $signature] = $parts;

        /**
         * Decode the JSON-encoded header and payload
         */
        $jsonHeader = $this->base64url_decode($header);
        $decodedHeader = json_decode($jsonHeader, true);
        if (
            !is_array($decodedHeader)
            || !isset($decodedHeader['alg'])
            || $decodedHeader['alg'] !== self::JWT_ALGORITHM
        ) {
            throw new \Exception('Invalid algorithm');
        }
        $jsonPayload = $this->base64url_decode($payload);
        $decodedPayload = json_decode($jsonPayload, true);
        if (!is_array($decodedPayload)) {
            throw new \Exception('Invalid token payload');
        }
        $decodedSignature = $this->base64url_decode($signature);

        /**
         * Verify the signature using the secret key and the algorithm specified in the header
         */
        $configuration = new \PDR\Application\Configuration();
        $secretKey = $configuration->getSecretKey();


        /**
         * Re-create the signature to compare with the one in the token
         */
        $expectedSignature = hash_hmac(
            self::HASH_ALGORITHM,
            $header . '.' . $payload,
            $secretKey,
            true
        );

        /**
         * Compare the expected signature with the one in the token
         */
        if (!hash_equals($decodedSignature, $expectedSignature)) {
            throw new \Exception('Invalid signature');
        }

        /**
         * Return the decoded payload
         */
        return $decodedPayload;
    }

    private function sendErrorAndExit(string $message, int $statusCode = 401): void {
        header('Content-Type: application/json', true, $statusCode);
        echo json_encode(['error' => $message]);
        exit;
    }
    private function base64url_encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64url_decode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
