<?php

namespace App\src\Services;

use GuzzleHttp\Client; // A popular HTTP client. You can use cURL as well.
use GuzzleHttp\Exception\RequestException;
use Exception;

/**
 * WhatsAppService
 *
 * A centralized service for sending WhatsApp messages via the Meta Cloud API.
 * It's designed to be multi-tenant, fetching credentials on a per-customer basis.
 */
class WhatsAppService
{
    /**
     * The base URI for the Meta WhatsApp Cloud API.
     * The {VERSION} and {PHONE_NUMBER_ID} will be replaced dynamically.
     */
    private const API_BASE_URI = 'https://graph.facebook.com/{VERSION}/{PHONE_NUMBER_ID}/messages';

    /**
     * @var Client The HTTP client for making API requests.
     */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    /**
     * Main method to send a WhatsApp message.
     *
     * @param int    $customerId The ID of your customer, used to fetch their credentials.
     * @param string $recipientPhoneNumber The phone number to send the message to (in international format).
     * @param array  $messagePayload The content of the message (e.g., text, document, template).
     *
     * @return array The result of the API call.
     */
    public function sendMessage(int $customerId, string $recipientPhoneNumber, array $messagePayload): array
    {
        try {
            // 1. Fetch the customer-specific credentials from your database.
            $credentials = $this->getCustomerCredentials($customerId);

            if (empty($credentials)) {
                throw new Exception("WhatsApp credentials not found for customer ID: {$customerId}");
            }

            // 2. Construct the full API URL.
            $apiUrl = str_replace(
                ['{VERSION}', '{PHONE_NUMBER_ID}'],
                [$credentials['api_version'], $credentials['phone_number_id']],
                self::API_BASE_URI
            );

            // 3. Build the final request body.
            $body = [
                'messaging_product' => 'whatsapp',
                'to' => $recipientPhoneNumber,
                ...$messagePayload // Merges the message-specific payload.
            ];

            // 4. Make the API request.
            $response = $this->httpClient->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $credentials['access_token'],
                    'Content-Type'  => 'application/json',
                ],
                'json' => $body,
            ]);

            return [
                'success' => true,
                'message' => 'Message sent successfully.',
                'data' => json_decode($response->getBody()->getContents(), true),
            ];

        } catch (RequestException $e) {
            // Catch specific HTTP-related errors from Guzzle.
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body.';
            return [
                'success' => false,
                'message' => 'API request failed.',
                'error_details' => json_decode($responseBody, true) ?? $responseBody,
            ];
        } catch (Exception $e) {
            // Catch other general errors (e.g., credentials not found).
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Creates a payload for a simple text message.
     *
     * @param string $text The body of the text message.
     * @return array
     */
    public static function createTextMessage(string $text): array
    {
        return [
            'type' => 'text',
            'text' => [
                'preview_url' => false, // Set to true to allow URL previews
                'body' => $text,
            ],
        ];
    }

    /**
     * Creates a payload for a document message.
     *
     * @param string $url The public URL of the document.
     * @param string|null $caption Optional caption for the document.
     * @param string|null $filename Optional filename for the document.
     * @return array
     */
    public static function createDocumentMessage(string $url, ?string $caption = null, ?string $filename = null): array
    {
        $documentData = ['link' => $url];
        if ($caption) {
            $documentData['caption'] = $caption;
        }
        if ($filename) {
            $documentData['filename'] = $filename;
        }

        return [
            'type' => 'document',
            'document' => $documentData,
        ];
    }
    
    /**
     * Creates a payload for a template message.
     *
     * @param string $templateName The name of the pre-approved WhatsApp template.
     * @param array $components The dynamic components for the template.
     * @param string $languageCode The language code for the template.
     * @return array
     */
    public static function createTemplateMessage(string $templateName, array $components, string $languageCode = 'en_US'): array
    {
        return [
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode
                ],
                'components' => $components
            ]
        ];
    }


    /**
     * Fetches WhatsApp credentials for a given customer.
     *
     * In a real application, this would query your database.
     *
     * @param int $customerId
     * @return array|null An associative array of credentials or null if not found.
     */
    private function getCustomerCredentials(int $customerId): ?array
    {
        // --- PSEUDO-CODE: Replace with your actual database logic ---
        // $db = new DatabaseConnection();
        // $stmt = $db->prepare("SELECT * FROM customer_whatsapp_credentials WHERE customer_id = ?");
        // $stmt->execute([$customerId]);
        // $credentials = $stmt->fetch();
        // return $credentials;
        // -----------------------------------------------------------

        // For demonstration purposes, we'll return a hardcoded array.
        // Make sure to replace this with your actual database query.
        if ($customerId === 1) {
            return [
                'customer_id'     => 1,
                'access_token'    => 'YOUR_CUSTOMER_A_PERMANENT_ACCESS_TOKEN',
                'phone_number_id' => 'YOUR_CUSTOMER_A_PHONE_NUMBER_ID',
                'waba_id'         => 'YOUR_CUSTOMER_A_WHATSAPP_BUSINESS_ACCOUNT_ID',
                'api_version'     => 'v18.0', // Or your desired API version
            ];
        }

        return null;
    }
}
