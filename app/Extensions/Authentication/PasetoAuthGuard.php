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

        if ($user && $this->provider->validateCredentials($user, $credentials)) {
            return $this->login($user);
        }

        return false;
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
    * Attempt to refresh the token
    *
    * @param  \Illuminate\Http\Request $request
    *
    * @return mixed
    * @throws Exception
    * @throws InvalidKeyException
    * @throws InvalidPurposeException
    * @throws PasetoException
    * @throws TypeError
    */
    public function refresh(Request $request)
    {
        $this->setRequest($request);
        $refreshToken = $this->getTokenFromRequest();
        try {
            $this->token = $this->parseToken($refreshToken);
            $this->user = $this->user();
            if ($this->user->refresh_token === $refreshToken) {
                return $this->generateTokenForUser();
            } else {
                return false;
            }
        } catch (PasetoException $e) {
            return false;
        }
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
        $refreshToken = $this->user->refresh_token;

        try {
            $this->parseToken($refreshToken);
        } catch (PasetoException $e) {
            $this->user->refresh_token = $this->generateRefreshTokenForUser();
            $this->user->save();
        }
        return $this->generateTokenForUser();
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
            ->setExpiration(Carbon::now()->addMinutes($this->getExpireTime()))
            ->setIssuer($this->getIssuer())
            ->setClaims($claims);

        return (string)$token;
    }

    /**
    * @return string
    * @throws InvalidKeyException
    * @throws InvalidPurposeException
    * @throws PasetoException
    * @throws TypeError
    */
    private function generateRefreshTokenForUser()
    {
        $claims = [
            'id' => $this->user->id
        ];

        $token = $this->getTokenBuilder()
        ->setExpiration(Carbon::now()->addDays($this->getRefreshExpireTime()))
        ->setIssuer($this->getIssuer())
        ->setClaims($claims);

        return (string)$token;
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

    /**
     * @return mixed
     */
    private function getIssuer()
    {
        return env('PASETO_AUTH_ISSUER');
    }

    /**
     * @return mixed
     */
    private function getExpireTime()
    {
        return env('PASETO_AUTH_EXPIRE_AFTER_HOURS');
    }

    /**
    * @return mixed
    */
    private function getRefreshExpireTime()
    {
        return env('PASETO_REFRESH_EXPIRE_AFTER_DAYS');
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
     * @return JsonToken
     * @throws TypeError
     * @throws InvalidVersionException
     * @throws PasetoException
     */
    private function parseToken($token)
    {
        $parser = Parser::getLocal($this->getSharedKey(), ProtocolCollection::v2());

        $parser->addRule(new NotExpired);
        $parser->addRule(new IssuedBy($this->getIssuer()));

        return $parser->parse($token);
    }
}
