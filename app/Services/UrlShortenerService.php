<?php

namespace App\Services;

use App\Models\Url;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UrlShortenerService
{
    /**
     * Shorten a URL with optional parameters
     */
    public function shortenUrl(array $data): Url
    {
        $validatedData = $this->validateUrlData($data);
        
        // Check for existing URL if user is provided
        if (isset($validatedData['user_id'])) {
            $existingUrl = $this->findExistingUrl($validatedData['original_url'], $validatedData['user_id']);
            if ($existingUrl) {
                return $existingUrl;
            }
        }

        // Generate short code
        $shortCode = $this->generateShortCode($validatedData['custom_code'] ?? null);
        
        // Extract URL metadata if title is not provided
        if (empty($validatedData['title'])) {
            $metadata = $this->extractUrlMetadata($validatedData['original_url']);
            $validatedData['title'] = $metadata['title'] ?? null;
            $validatedData['description'] = $metadata['description'] ?? null;
        }

        // Create the URL record
        return Url::create([
            'original_url' => $validatedData['original_url'],
            'short_code' => $shortCode,
            'user_id' => $validatedData['user_id'] ?? null,
            'title' => $validatedData['title'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'expires_at' => $validatedData['expires_at'] ?? null,
            'password_hash' => isset($validatedData['password']) ? bcrypt($validatedData['password']) : null,
            'is_active' => true
        ]);
    }

    /**
     * Validate URL data
     */
    private function validateUrlData(array $data): array
    {
        $validator = Validator::make($data, [
            'original_url' => 'required|url|max:2048',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'custom_code' => 'nullable|string|min:3|max:10|alpha_num|unique:urls,short_code',
            'expires_at' => 'nullable|date|after:now',
            'password' => 'nullable|string|min:4|max:255'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();
        
        // Additional URL validation
        if (!$this->isValidUrl($validated['original_url'])) {
            throw ValidationException::withMessages([
                'original_url' => ['The URL appears to be invalid or potentially malicious.']
            ]);
        }

        return $validated;
    }

    /**
     * Generate a unique short code
     */
    private function generateShortCode(?string $customCode = null): string
    {
        if ($customCode) {
            return $customCode;
        }

        $attempts = 0;
        $maxAttempts = 10;

        do {
            $shortCode = $this->generateRandomCode();
            $attempts++;
            
            if ($attempts > $maxAttempts) {
                throw new \Exception('Unable to generate unique short code after ' . $maxAttempts . ' attempts');
            }
        } while (Url::where('short_code', $shortCode)->exists());

        return $shortCode;
    }

    /**
     * Generate a random alphanumeric code
     */
    private function generateRandomCode(int $length = 6): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $code;
    }

    /**
     * Find existing URL for a user
     */
    private function findExistingUrl(string $originalUrl, int $userId): ?Url
    {
        return Url::where('original_url', $originalUrl)
                  ->where('user_id', $userId)
                  ->where('is_active', true)
                  ->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now())
                  ->first();
    }

    /**
     * Validate URL for security and accessibility
     */
    private function isValidUrl(string $url): bool
    {
        // Parse URL
        $parsedUrl = parse_url($url);
        
        if (!$parsedUrl || !isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            return false;
        }

        // Check allowed schemes
        $allowedSchemes = ['http', 'https'];
        if (!in_array(strtolower($parsedUrl['scheme']), $allowedSchemes)) {
            return false;
        }

        // Check for localhost or internal IPs
        $host = strtolower($parsedUrl['host']);
        if (in_array($host, ['localhost', '127.0.0.1', '0.0.0.0']) || 
            preg_match('/^192\.168\./', $host) ||
            preg_match('/^10\./', $host) ||
            preg_match('/^172\.1[6-9]\./', $host) ||
            preg_match('/^172\.2[0-9]\./', $host) ||
            preg_match('/^172\.3[0-1]\./', $host)) {
            return false;
        }

        // Basic malicious domain check (can be extended with a blocklist)
        $suspiciousDomains = ['bit.ly', 'tinyurl.com', 'short.link', 't.co', 'goo.gl'];
        foreach ($suspiciousDomains as $domain) {
            if (strpos($host, $domain) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extract metadata from URL (basic implementation)
     */
    private function extractUrlMetadata(string $url): array
    {
        $metadata = ['title' => null, 'description' => null];
        
        try {
            // Create a context with a timeout
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'URL Shortener Bot 1.0'
                ]
            ]);
            
            $html = @file_get_contents($url, false, $context);
            
            if ($html) {
                // Extract title
                if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
                    $metadata['title'] = trim(html_entity_decode($matches[1]));
                }
                
                // Extract description
                if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']*)["\'][^>]*>/i', $html, $matches)) {
                    $metadata['description'] = trim(html_entity_decode($matches[1]));
                }
            }
        } catch (\Exception $e) {
            // Silently fail and return empty metadata
        }
        
        return $metadata;
    }

    /**
     * Get URL by short code
     */
    public function getUrlByShortCode(string $shortCode): ?Url
    {
        return Url::where('short_code', $shortCode)->first();
    }

    /**
     * Check if URL is accessible (not expired, active, etc.)
     */
    public function isUrlAccessible(Url $url): bool
    {
        return $url->isAccessible();
    }

    /**
     * Validate password for protected URL
     */
    public function validateUrlPassword(Url $url, string $password): bool
    {
        if (!$url->isPasswordProtected()) {
            return true; // If URL is not password protected, access is allowed
        }

        return password_verify($password, $url->password_hash);
    }
} 