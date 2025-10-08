<?php
namespace App\Services;

use App\Entity\User;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class CsrfTokenService {

    private $tokenManager;
    public function __construct(CsrfTokenManagerInterface $tokenManager, ValidatorInterface $validator,
                                RefreshTokenManagerInterface $refreshTokenManager, JWTTokenManagerInterface $JWTTokenManager) {
        $this->tokenManager = $tokenManager;
        $this->validator = $validator;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->JWTTokenManager = $JWTTokenManager;
    }

    /**
     * Create Token
     */
    public function getTokenValue($identity) {
        return $this->tokenManager->getToken($identity)->getValue();
    }

    //ATTENTION NE PAS UTULISER VIA API, UTILISER isValidTokenForRequest !
    public function isValidToken($identity, $tokenValue) {
        return $this->tokenManager->isTokenValid(new CsrfToken($identity, $tokenValue));
//        if($this->getTokenValue($identity) == $tokenValue) {
//            return true;
//        }
//        return false;
    }

    /**
     *
     * JWT API : pas besoin de CSRF car la request est déjà protégé par le bearer token
     * @param $identity
     * @param $tokenValue
     * @param Request $request
     * @return bool
     */
    public function isValidTokenForRequest($identity, $tokenValue, Request $request) {
        $firewallContext = $request->get('_firewall_context');
        if($firewallContext == "security.firewall.map.context.api") {
            return true;
        }
        return $this->tokenManager->isTokenValid(new CsrfToken($identity, $tokenValue));
    }

    //if change mail create new Token !!
    public function createNewJWT(User $user) {
        $token = $this->JWTTokenManager->create($user);
        $ttl = 10800; //as conf
        $datetime = new \DateTime();
        $datetime->modify('+'.$ttl.' seconds');
        $refreshToken = $this->refreshTokenManager->create();
        $refreshToken->setUsername($user->getEmail());
        $refreshToken->setRefreshToken();
        $refreshToken->setValid($datetime);
        // Validate, that the new token is a unique refresh token
        $valid = false;
        while (false === $valid) {
            $valid = true;
            $errors = $this->validator->validate($refreshToken);
            if ($errors->count() > 0) {
                foreach ($errors as $error) {
                    if ('refreshToken' === $error->getPropertyPath()) {
                        $valid = false;
                        $refreshToken->setRefreshToken();
                    }
                }
            }
        }
        $this->refreshTokenManager->save($refreshToken);
        return  [
            'newToken' => $token,
            'refreshToken' => $refreshToken->getRefreshToken()
        ];
    }
}