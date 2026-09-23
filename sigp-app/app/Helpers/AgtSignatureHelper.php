<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class AgtSignatureHelper
{
    /**
     * Generates an RSA 2048 key pair for AGT signature if it doesn't exist.
     */
    public static function generateKeys(): array
    {
        // Garante que existe um openssl.cnf básico para o Windows não falhar
        $cnfPath = storage_path('app/agt/openssl.cnf');
        if (!file_exists($cnfPath)) {
            if (!file_exists(storage_path('app/agt'))) {
                mkdir(storage_path('app/agt'), 0755, true);
            }
            file_put_contents($cnfPath, "[ req ]\ndistinguished_name = req_distinguished_name\n[ req_distinguished_name ]");
        }

        $config = [
            "digest_alg" => "sha1",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "config" => $cnfPath
        ];

        // Create the private and public key
        $res = openssl_pkey_new($config);
        
        if (!$res) {
            throw new \Exception('Failed to generate private key: ' . openssl_error_string());
        }

        // Extract the private key
        openssl_pkey_export($res, $privateKey, null, $config);

        // Extract the public key
        $publicKey = openssl_pkey_get_details($res)["key"];

        // Store them securely
        Storage::put('agt/private_key.pem', $privateKey);
        Storage::put('agt/public_key.pem', $publicKey);

        return [
            'private' => $privateKey,
            'public' => $publicKey,
        ];
    }

    /**
     * Calculate the Hash of an invoice based on AGT rules.
     * Formula: DataEmissao;DataSistema;NumeroFatura;ValorTotal;HashAnterior
     * 
     * @param string $dataEmissao (YYYY-MM-DD)
     * @param string $dataSistema (YYYY-MM-DDTHH:MM:SS)
     * @param string $numeroFatura (e.g. FT 2026/1)
     * @param float $valorTotal (Gross total of the document)
     * @param string|null $hashAnterior (Hash of the previous document in the same series, empty if first)
     * 
     * @return string (Base64 encoded RSA-SHA1 signature)
     */
    public static function signInvoice($dataEmissao, $dataSistema, $numeroFatura, $valorTotal, $hashAnterior = "")
    {
        // 1. Prepare the string to sign
        $valorTotalStr = number_format($valorTotal, 2, '.', '');
        
        $stringToSign = "{$dataEmissao};{$dataSistema};{$numeroFatura};{$valorTotalStr};{$hashAnterior}";

        // 2. Load the private key
        $privateKeyPath = storage_path('app/agt/private_key.pem');
        if (!file_exists($privateKeyPath)) {
            throw new \Exception("Chave privada RSA da AGT não encontrada. Execute 'php artisan agt:generate-keys'.");
        }

        $privateKey = file_get_contents($privateKeyPath);
        $keyId = openssl_pkey_get_private($privateKey);

        if (!$keyId) {
            throw new \Exception("Chave privada RSA inválida.");
        }

        // 3. Sign the string using RSA-SHA1
        $signature = '';
        if (!openssl_sign($stringToSign, $signature, $keyId, OPENSSL_ALGO_SHA1)) {
            throw new \Exception("Falha ao assinar a string com RSA.");
        }

        // 4. Return the Base64 encoded signature
        return base64_encode($signature);
    }
}
