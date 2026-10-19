<?php

namespace App\Services\Document;

use Illuminate\Support\Facades\Log;

/**
 * End-to-End Encryption Service for Meeting Documents
 *
 * This service provides a design for client-side end-to-end encryption.
 * The server NEVER sees encryption keys - they are generated and managed
 * entirely on the client side. The server only stores encrypted blobs.
 *
 * SECURITY NOTICE:
 * - This is a DESIGN DOCUMENT showing how E2E encryption would work
 * - The actual encryption/decryption MUST happen in the browser (JavaScript)
 * - Server-side methods here are for key management coordination ONLY
 * - Never log, store, or transmit encryption keys to the server
 *
 * KEY MANAGEMENT FLOW:
 * 1. Organizer enables E2E encryption for a document
 * 2. Client generates a random document key (256-bit AES-GCM)
 * 3. Client encrypts document with document key
 * 4. For each authorized recipient:
 *    a. Client fetches recipient's public key (from key registry)
 *    b. Client encrypts document key with recipient's public key (RSA-OAEP or ECIES)
 *    c. Client sends encrypted key package to server
 * 5. Server stores encrypted document + encrypted key packages
 * 6. When recipient wants to access:
 *    a. Client fetches encrypted document + their encrypted key package
 *    b. Client decrypts document key with their private key
 *    c. Client decrypts document with document key
 *
 * KEY REGISTRY:
 * - Users generate their own key pairs on first login
 * - Public keys are stored in a key registry (can be on server)
 * - Private keys NEVER leave the user's device
 * - Key rotation supported via key versioning
 */
class E2EEncryptionService
{
    public const ALGORITHM_AES_GCM = 'AES-256-GCM';

    public const ALGORITHM_RSA_OAEP = 'RSA-OAEP-256';

    public const KEY_SIZE_BITS = 256;

    public const IV_SIZE_BYTES = 12; // 96 bits for GCM

    public const TAG_SIZE_BYTES = 16; // 128 bits

    /**
     * Generate a new document encryption key (client-side).
     * This method should be called from JavaScript in the browser.
     *
     * @return string Base64-encoded 256-bit key
     */
    public static function generateDocumentKey(): string
    {
        // In production, this should be done client-side using Web Crypto API:
        // const key = await crypto.subtle.generateKey(
        //   { name: "AES-GCM", length: 256 },
        //   true, // extractable
        //   ["encrypt", "decrypt"]
        // );
        // return btoa(String.fromCharCode(...new Uint8Array(await crypto.subtle.exportKey("raw", key))));

        // Server-side fallback for testing ONLY - NOT for production use!
        return base64_encode(random_bytes(self::KEY_SIZE_BITS / 8));
    }

    /**
     * Encrypt document content with AES-GCM (client-side).
     *
     * @param  string  $content  Raw document content
     * @param  string  $key  Base64-encoded document key
     * @return array{iv: string, ciphertext: string, tag: string} All base64-encoded
     */
    public static function encryptDocument(string $content, string $key): array
    {
        // In production, this should be done client-side using Web Crypto API:
        // const iv = crypto.getRandomValues(new Uint8Array(12));
        // const encrypted = await crypto.subtle.encrypt(
        //   { name: "AES-GCM", iv },
        //   key,
        //   new TextEncoder().encode(content)
        // );
        // const ciphertext = new Uint8Array(encrypted.slice(0, -16));
        // const tag = new Uint8Array(encrypted.slice(-16));
        // return { iv: btoa(...), ciphertext: btoa(...), tag: btoa(...) };

        $keyBytes = base64_decode($key);
        $iv = random_bytes(self::IV_SIZE_BYTES);

        $ciphertext = openssl_encrypt(
            $content,
            'aes-256-gcm',
            $keyBytes,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return [
            'iv' => base64_encode($iv),
            'ciphertext' => base64_encode($ciphertext),
            'tag' => base64_encode($tag),
        ];
    }

    /**
     * Decrypt document content with AES-GCM (client-side).
     *
     * @param  string  $ciphertext  Base64-encoded ciphertext
     * @param  string  $key  Base64-encoded document key
     * @param  string  $iv  Base64-encoded IV
     * @param  string  $tag  Base64-encoded auth tag
     * @return string Decrypted content
     */
    public static function decryptDocument(string $ciphertext, string $key, string $iv, string $tag): string
    {
        // In production, this should be done client-side using Web Crypto API:
        // const decrypted = await crypto.subtle.decrypt(
        //   { name: "AES-GCM", iv: new Uint8Array(atob(iv)) },
        //   key,
        //   new Uint8Array([...atob(ciphertext), ...atob(tag)])
        // );
        // return new TextDecoder().decode(decrypted);

        $keyBytes = base64_decode($key);
        $ivBytes = base64_decode($iv);
        $tagBytes = base64_decode($tag);
        $ciphertextBytes = base64_decode($ciphertext);

        $plaintext = openssl_decrypt(
            $ciphertextBytes,
            'aes-256-gcm',
            $keyBytes,
            OPENSSL_RAW_DATA,
            $ivBytes,
            $tagBytes
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed - invalid key or corrupted data');
        }

        return $plaintext;
    }

    /**
     * Encrypt document key for a recipient using their public key (client-side).
     *
     * @param  string  $documentKey  Base64-encoded document key
     * @param  string  $recipientPublicKeyPem  Recipient's public key in PEM format
     * @return string Base64-encoded encrypted key
     */
    public static function encryptKeyForRecipient(string $documentKey, string $recipientPublicKeyPem): string
    {
        // In production, this should be done client-side using Web Crypto API:
        // const publicKey = await crypto.subtle.importKey(
        //   "spki",
        //   str2ab(recipientPublicKeyPem),
        //   { name: "RSA-OAEP", hash: "SHA-256" },
        //   false,
        //   ["encrypt"]
        // );
        // const encryptedKey = await crypto.subtle.encrypt(
        //   { name: "RSA-OAEP" },
        //   publicKey,
        //   str2ab(documentKey)
        // );
        // return btoa(String.fromCharCode(...new Uint8Array(encryptedKey)));

        $publicKey = openssl_pkey_get_public($recipientPublicKeyPem);
        if (! $publicKey) {
            throw new \InvalidArgumentException('Invalid public key');
        }

        $keyBytes = base64_decode($documentKey);
        $encrypted = '';
        $success = openssl_public_encrypt($keyBytes, $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);
        openssl_free_key($publicKey);

        if (! $success) {
            throw new \RuntimeException('Key encryption failed');
        }

        return base64_encode($encrypted);
    }

    /**
     * Decrypt document key with recipient's private key (client-side).
     *
     * @param  string  $encryptedKey  Base64-encoded encrypted key
     * @param  string  $privateKeyPem  Recipient's private key in PEM format
     * @return string Base64-encoded document key
     */
    public static function decryptKeyForRecipient(string $encryptedKey, string $privateKeyPem): string
    {
        // In production, this should be done client-side using Web Crypto API:
        // const privateKey = await crypto.subtle.importKey(
        //   "pkcs8",
        //   str2ab(privateKeyPem),
        //   { name: "RSA-OAEP", hash: "SHA-256" },
        //   false,
        //   ["decrypt"]
        // );
        // const decryptedKey = await crypto.subtle.decrypt(
        //   { name: "RSA-OAEP" },
        //   privateKey,
        //   str2ab(encryptedKey)
        // );
        // return btoa(String.fromCharCode(...new Uint8Array(decryptedKey)));

        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if (! $privateKey) {
            throw new \InvalidArgumentException('Invalid private key');
        }

        $encryptedBytes = base64_decode($encryptedKey);
        $decrypted = '';
        $success = openssl_private_decrypt($encryptedBytes, $decrypted, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);
        openssl_free_key($privateKey);

        if (! $success) {
            throw new \RuntimeException('Key decryption failed');
        }

        return base64_encode($decrypted);
    }

    /**
     * Store encrypted key package for a recipient (server-side coordination).
     * This is called by the client after encrypting the document key for each recipient.
     *
     * @param  string  $encryptedKey  Base64-encoded encrypted document key
     * @param  string  $keyVersion  Version of the recipient's public key used
     */
    public static function storeEncryptedKeyPackage(int $documentId, int $recipientParticipantId, string $encryptedKey, string $keyVersion = '1'): void
    {
        // In production, store in a document_encryption_keys table:
        // DB::table('document_encryption_keys')->insert([
        //     'document_share_id' => $documentId,
        //     'participant_id' => $recipientParticipantId,
        //     'encrypted_key' => $encryptedKey,
        //     'key_version' => $keyVersion,
        //     'created_at' => now(),
        // ]);

        Log::info('E2E: Encrypted key package stored', [
            'document_id' => $documentId,
            'recipient_id' => $recipientParticipantId,
            'key_version' => $keyVersion,
        ]);
    }

    /**
     * Retrieve encrypted key package for a recipient (server-side coordination).
     *
     * @return string|null Base64-encoded encrypted key or null if not found
     */
    public static function getEncryptedKeyPackage(int $documentId, int $recipientParticipantId): ?string
    {
        // In production:
        // return DB::table('document_encryption_keys')
        //     ->where('document_share_id', $documentId)
        //     ->where('participant_id', $recipientParticipantId)
        //     ->value('encrypted_key');

        return null;
    }

    /**
     * Register a user's public key (server-side).
     * Called when user generates a key pair on first login.
     *
     * @param  string  $publicKeyPem  Public key in PEM format
     */
    public static function registerPublicKey(int $userId, string $publicKeyPem, string $keyVersion = '1'): void
    {
        // Validate key format
        $key = openssl_pkey_get_public($publicKeyPem);
        if (! $key) {
            throw new \InvalidArgumentException('Invalid public key format');
        }
        openssl_free_key($key);

        // In production, store in user_encryption_keys table:
        // DB::table('user_encryption_keys')->upsert([
        //     'user_id' => $userId,
        //     'public_key_pem' => $publicKeyPem,
        //     'key_version' => $keyVersion,
        //     'updated_at' => now(),
        // ], ['user_id']);

        Log::info('E2E: Public key registered', [
            'user_id' => $userId,
            'key_version' => $keyVersion,
        ]);
    }

    /**
     * Get a user's public key (server-side).
     *
     * @return string|null Public key in PEM format
     */
    public static function getPublicKey(int $userId): ?string
    {
        // In production:
        // return DB::table('user_encryption_keys')
        //     ->where('user_id', $userId)
        //     ->value('public_key_pem');

        return null;
    }

    /**
     * Get public keys for multiple users (server-side).
     *
     * @param  array<int>  $userIds
     * @return array<int, string> Map of user_id => public_key_pem
     */
    public static function getPublicKeys(array $userIds): array
    {
        // In production:
        // return DB::table('user_encryption_keys')
        //     ->whereIn('user_id', $userIds)
        //     ->pluck('public_key_pem', 'user_id')
        //     ->toArray();

        return [];
    }

    /**
     * Rotate a user's key pair (server-side coordination).
     * Called when user wants to generate a new key pair.
     */
    public static function rotateKey(int $userId, string $newPublicKeyPem, string $newKeyVersion): void
    {
        // In production, this would:
        // 1. Store new public key with new version
        // 2. Keep old public key for decrypting existing documents
        // 3. Client re-encrypts document keys for all documents they can access
        // 4. Server updates encrypted key packages

        Log::info('E2E: Key rotation initiated', [
            'user_id' => $userId,
            'new_key_version' => $newKeyVersion,
        ]);
    }

    /**
     * Client-side JavaScript implementation reference.
     * This is provided as documentation for frontend implementation.
     */
    public static function getClientImplementationReference(): string
    {
        return <<<'JS'
// ============================================
// CLIENT-SIDE E2E ENCRYPTION IMPLEMENTATION
// ============================================

// 1. Generate user key pair on first login
async function generateUserKeyPair() {
  const keyPair = await crypto.subtle.generateKey(
    {
      name: "RSA-OAEP",
      modulusLength: 2048,
      publicExponent: new Uint8Array([1, 0, 1]),
      hash: "SHA-256",
    },
    true, // extractable
    ["encrypt", "decrypt"]
  );

  // Export keys
  const publicKey = await crypto.subtle.exportKey("spki", keyPair.publicKey);
  const privateKey = await crypto.subtle.exportKey("pkcs8", keyPair.privateKey);

  // Store private key securely (IndexedDB, encrypted with user password)
  // Send public key to server for registration
  return {
    publicKey: btoa(String.fromCharCode(...new Uint8Array(publicKey))),
    privateKey: btoa(String.fromCharCode(...new Uint8Array(privateKey))),
  };
}

// 2. Encrypt document for upload
async function encryptDocumentForUpload(file, recipients) {
  // Generate document key
  const docKey = await crypto.subtle.generateKey(
    { name: "AES-GCM", length: 256 },
    true,
    ["encrypt", "decrypt"]
  );

  // Encrypt file content
  const fileData = await file.arrayBuffer();
  const iv = crypto.getRandomValues(new Uint8Array(12));
  const encrypted = await crypto.subtle.encrypt(
    { name: "AES-GCM", iv },
    docKey,
    fileData
  );

  // Split ciphertext and tag
  const ciphertext = new Uint8Array(encrypted.slice(0, -16));
  const tag = new Uint8Array(encrypted.slice(-16));

  // Export document key
  const rawDocKey = await crypto.subtle.exportKey("raw", docKey);

  // Encrypt document key for each recipient
  const keyPackages = {};
  for (const recipient of recipients) {
    const publicKey = await importPublicKey(recipient.publicKeyPem);
    const encryptedKey = await crypto.subtle.encrypt(
      { name: "RSA-OAEP" },
      publicKey,
      rawDocKey
    );
    keyPackages[recipient.userId] = btoa(String.fromCharCode(...new Uint8Array(encryptedKey)));
  }

  // Upload encrypted document + key packages
  const formData = new FormData();
  formData.append("encrypted_file", new Blob([ciphertext]), file.name + ".enc");
  formData.append("iv", btoa(String.fromCharCode(...iv)));
  formData.append("tag", btoa(String.fromCharCode(...tag)));
  formData.append("key_packages", JSON.stringify(keyPackages));
  formData.append("encrypted_e2e", "true");

  return formData;
}

// 3. Decrypt document for preview/download
async function decryptDocument(encryptedFile, iv, tag, encryptedKey, privateKey) {
  // Import private key
  const privKey = await importPrivateKey(privateKey);

  // Decrypt document key
  const rawDocKey = await crypto.subtle.decrypt(
    { name: "RSA-OAEP" },
    privKey,
    str2ab(encryptedKey)
  );

  // Import document key
  const docKey = await crypto.subtle.importKey(
    "raw",
    rawDocKey,
    { name: "AES-GCM" },
    false,
    ["decrypt"]
  );

  // Decrypt file
  const decrypted = await crypto.subtle.decrypt(
    { name: "AES-GCM", iv: str2ab(iv) },
    docKey,
    concatArrays(str2ab(encryptedFile), str2ab(tag))
  );

  return decrypted;
}

// Helper functions
function str2ab(str) { return new Uint8Array(atob(str).split('').map(c => c.charCodeAt(0))); }
function concatArrays(a, b) { const c = new Uint8Array(a.length + b.length); c.set(a); c.set(b, a.length); return c; }
async function importPublicKey(pem) { /* ... */ }
async function importPrivateKey(pem) { /* ... */ }
JS;
    }
}
