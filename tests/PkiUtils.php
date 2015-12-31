<?php
namespace Jeskew\Cache;

trait PkiUtils
{
    /** @var string */
    private static $certificate;
    /** @var string */
    private static $key;

    private static function setUpCertAndKey()
    {
        if (empty(self::$certificate) || empty(self::$key)) {
            // create a new key pair
            $pKey = openssl_pkey_new();

            // extract the private key
            openssl_pkey_export($pKey, self::$key);

            // extract the public key
            $csr = openssl_csr_new([], $pKey);
            $x509 = openssl_csr_sign($csr, null, $pKey, 1);
            openssl_x509_export($x509, self::$certificate);

            // clean up the created artifacts
            openssl_x509_free($x509);
            openssl_pkey_free($pKey);
        }
    }

    private static function getCertificate()
    {
        self::setUpCertAndKey();

        return self::$certificate;
    }

    private static function getKey()
    {
        self::setUpCertAndKey();

        return self::$key;
    }
}
