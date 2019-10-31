<?php

namespace League\OAuth2\Client\Token;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use InvalidArgumentException;

class AppleAccessToken extends AccessToken
{
    /**
     * @var string
     */
    protected $idToken;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var boolean
     */
    protected $isPrivateEmail;

    /**
     * Constructs an access token.
     *
     * @param  array $options An array of options returned by the service provider
     *     in the access token request. The `access_token` option is required.
     * @throws InvalidArgumentException if `access_token` is not provided in `$options`.
     */
    public function __construct(array $options = [])
    {
        if (empty($options['id_token'])) {
            throw new InvalidArgumentException('Required option not passed: "id_token"');
        }

        $decoded = JWT::decode($options['id_token'], $this->getAppleKey(), ['RS256']);
        $payload = json_decode(json_encode($decoded), true);

        $options['resource_owner_id'] = $payload['sub'];

        if (isset($payload['email_verified']) && $payload['email_verified']) {
            $options['email'] = $payload['email'];
        }

        if (isset($payload['is_private_email'])) {
            $this->isPrivateEmail = $payload['is_private_email'];
        }

        parent::__construct($options);

        if (isset($options['id_token'])) {
            $this->idToken = $options['id_token'];
        }

        if (isset($options['email'])) {
            $this->email = $options['email'];
        }
    }

    /**
     * @return array Apple's JSON Web Key
     */
    protected function getAppleKey()
    {
        return JWK::parseKeySet(file_get_contents('https://appleid.apple.com/auth/keys'))['AIDOPK1'];
    }

    /**
     * @return string
     */
    public function getIdToken()
    {
        return $this->idToken;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return boolean
     */
    public function isPrivateEmail()
    {
        return $this->isPrivateEmail;
    }
}
