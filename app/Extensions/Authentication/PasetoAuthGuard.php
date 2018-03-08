<?php

namespace App\Extensions\Authentication;

use App\User;
use Carbon\Carbon;
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
     * @throws \ParagonIE\Paseto\Exception\InvalidVersionException
     * @throws PasetoException
     */
    public function check()
    {
        $parser = Parser::getLocal($this->getSharedKey(), ProtocolCollection::v2());

        try {
            $this->token = $parser->parse($this->getTokenFromRequest());
        } catch (PasetoException $ex) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     * @throws TypeError
     * @throws \ParagonIE\Paseto\Exception\InvalidVersionException
     * @throws PasetoException
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Attempt to authenticate the user using the given credentials and return the token.
     *
     * @param array $credentials
     *
     * @return mixed
     * @throws TypeError
     * @throws \Exception
     * @throws \ParagonIE\Paseto\Exception\InvalidKeyException
     * @throws \ParagonIE\Paseto\Exception\InvalidPurposeException
     * @throws PasetoException
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
     * @param $user
     * @return string
     * @throws TypeError
     * @throws \Exception
     * @throws \ParagonIE\Paseto\Exception\InvalidKeyException
     * @throws \ParagonIE\Paseto\Exception\InvalidPurposeException
     * @throws PasetoException
     */
    private function login($user)
    {
        $this->setUser($user);

        return $this->generateTokenForUser();
    }

    /**
     * @throws \ParagonIE\Paseto\Exception\InvalidKeyException
     * @throws \ParagonIE\Paseto\Exception\InvalidPurposeException
     * @throws PasetoException
     * @throws TypeError
     * @throws \Exception
     */
    private function generateTokenForUser()
    {
        $token = (new Builder)
            ->setKey($this->getSharedKey())
            ->setVersion(new Version2)
            ->setPurpose(Purpose::local())
            // Set it to expire in one day
            ->setExpiration(
                Carbon::now()->addSeconds(env('PASETO_AUTH_EXPIRE_AFTER'))
            )
            ->setIssuer(env('PASETO_AUTH_ISSUER'))
            // Store arbitrary data
            ->setClaims([
                'id' => $this->user->id
            ]);

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
     * @return mixed
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
}
