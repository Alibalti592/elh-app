<?php
namespace App\Services;

/**
 * Class UrlEncryptorService
 */
class UrlEncryptorService
{
    const CIPHER_ALGORITHM = 'aes-256-ctr';
    const HASH_ALGORITHM = 'sha256';
    const SECRET_KEY = 'QHur2q54dLoprmQn';
    const SECRET_IV = '65JkoPqmaeYdWb';


    private $secretKey;
    private $iv;
    private $cipherAlgorithm;


    public function __construct() {
        $this->cipherAlgorithm = self::CIPHER_ALGORITHM;
        if (!\in_array($this->cipherAlgorithm, openssl_get_cipher_methods(true))) {
            throw new \InvalidArgumentException(
                "NzoUrlEncryptor:: - unknown cipher algorithm {$this->cipherAlgorithm}"
            );
        }
        $this->secretKey = self::SECRET_KEY;
        $this->iv = substr(hash_hmac(self::HASH_ALGORITHM, self::SECRET_IV, $this->secretKey, true), 0, openssl_cipher_iv_length($this->cipherAlgorithm));
    }

    /**
     * @param string $plainText
     * @return string
     */
    public function encrypt($plainText)
    {
        $encrypted = openssl_encrypt($plainText, $this->cipherAlgorithm, $this->secretKey, OPENSSL_RAW_DATA, $this->iv);
        return $this->base64UrlEncode($encrypted);
    }

    /**
     * @param string $encrypted
     * @return string
     */
    public function decrypt($encrypted)
    {
        $decrypted = openssl_decrypt(
            $this->base64UrlDecode($encrypted),
            $this->cipherAlgorithm,
            $this->secretKey,
            OPENSSL_RAW_DATA,
            $this->iv
        );

        return trim($decrypted);
    }

    /**
     * @param string $data
     * @return string
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param string $data
     * @return string
     */
    private function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public function getCoachIdentifier($coach, $autoValidateByCustomer = false) {
        return $this->encrypt($coach->getId()."-".$coach->getCustomer()->getId()."-".$autoValidateByCustomer."-Lqmpd415qmrposKSk");
    }

    public function getCustomerIdentifier($customer, $autoValidateByCustomer = false) {
        return $this->encrypt("coachNone-".$customer->getId()."-".$autoValidateByCustomer."-Kpemd41LqopJkspk");
    }

    public function getEncryptedUserId($userId, $currentUserId) {
        return $this->encrypt($userId + $currentUserId);
    }

    public function getUncryptedUserId($encryptId, $currentUserId) {
        return intval($this->decrypt($encryptId)) - $currentUserId;
    }


    public function decryptFromFront($encrypted) {
        $key = hex2bin("dba92da2fc4f8c68951f2f2e19cd7b2c");
        $iv =  hex2bin("9cb8d70a5d525b31916c793cb0bbd6e3");
        $decrypted = openssl_decrypt($encrypted, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
        return trim($decrypted);
    }
}