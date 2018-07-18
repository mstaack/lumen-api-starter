<?php

namespace App\Extensions\Authentication;

use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\JsonToken;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Protocol\Version2;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\Rules\IssuedBy;
use ParagonIE\Paseto\Rules\NotExpired;
use TypeError;

class PasetoAuthGuard implements Guard
{
    /**
     * @var UserProvider
     */
    private $provider;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @var JsonToken|null
     */
    private $token;

    /**
     * PasetoAuthGuard constructor.
     * @param UserProvider $provider
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Determine if the current request is a guest user.
     *
     * @return bool
     * @throws TypeError
     * @throws InvalidVersionException
     * @throws PasetoException
     */
    public function guest()
    {
        if ($this->user !== null) {
            return false;
        }

        return !$this->check();
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     * @throws TypeError
     * @throws InvalidVersionException
     * @throws PasetoException
     */
    public function check()
    {
        $parser = Parser::getLocal($this->getSharedKey(), ProtocolCollection::v2());

        $parser->addRule(new NotExpired);
        $parser->addRule(new IssuedBy($this->getIssuer()));

        try {
            $this->token = $parser->parse($this->getTokenFromRequest());
        } catch (PasetoException $e) {
            return false;
        }

        return true;
    }

    /**
     * @return SymmetricKey
     * @throws TypeError
     */
    private function getSharedKey()
    {
        return SymmetricKey::fromEncodedString(env('PASETO_AUTH_KEY'));
    }

    /**
     * @return mixed
     */
    private function getIssuer()
    {
        return env('PASETO_AUTH_ISSUER');
    }

    /**
     * @return string|bool
     */
    private function getTokenFromRequest()
    {
        if ($token = $this->request->headers->get('Authorization')) {
            return str_after($token, 'Bearer ');
        }

        return false;
    }

    /**
     * Attempt to authenticate the user using the given credentials and return the token.
     *
     * @param array $credentials
     *
     * @return mixed
     * @throws Exception
     * @throws InvalidKeyException
     * @throws InvalidPurposeException
     * @throws PasetoException
     * @throws TypeError
     */
    public function attempt(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($user && $user->verified && $this->provider->validateCredentials($user, $credentials)) {
            return $this->login($user);
        }

        return false;
    }

    /**
     * @param $user
     * @return string
     * @throws TypeError
     * @throws Exception
     * @throws InvalidKeyException
     * @throws PasetoException
     * @throws InvalidPurposeException
     */
    private function login($user)
    {
        $this->setUser($user);

        return $this->generateTokenForUser();
    }

    /**
     * Set the current user.
     *
     * @param  Authenticatable $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     * @throws InvalidKeyException
     * @throws InvalidPurposeException
     * @throws PasetoException
     * @throws TypeError
     */
    private function generateTokenForUser()
    {
        $claims = [
            'id' => $this->user->id
        ];

        $token = $this->getTokenBuilder()
            ->setExpiration(Carbon::now()->addHours($this->getExpireTime()))
            ->setIssuer($this->getIssuer())
            ->setClaims($claims);

        return (string)$token;
    }

    /**
     * @return Builder
     * @throws PasetoException
     * @throws TypeError
     * @throws InvalidKeyException
     * @throws InvalidPurposeException
     */
    private function getTokenBuilder()
    {
        return (new Builder)
            ->setKey($this->getSharedKey())
            ->setVersion(new Version2)
            ->setPurpose(Purpose::local());
    }

    /**
     * @return mixed
     */
    private function getExpireTime()
    {
        return env('PASETO_AUTH_EXPIRE_AFTER_HOURS');
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws PasetoException
     */
    public function user()
    {
        if (!$this->user && $this->token) {
            $this->user = $this->provider->retrieveById($this->token->get('id'));
        }

        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        return $this->user->id;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return $this->provider->validateCredentials($this->user, $credentials);
    }

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
