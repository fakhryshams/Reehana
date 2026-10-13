<?php

namespace App\Services;

use App\Exceptions\FedExAddressValidationException;
use App\Exceptions\FedExRateException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\AdminModel\Master\WarehouseAddress;
use App\AdminModel\AdminCountry;
use Exception;
use App\AdminModel\City\AdminCityDescription;
use Illuminate\Support\Facades\Log;

class FedExService
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $accountNumber;
    protected $documentUploadBaseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.fedex.base_url');
        $this->clientId = config('services.fedex.key');
        $this->clientSecret = config('services.fedex.secret');
        $this->accountNumber = config('services.fedex.account_number');
        $this->documentUploadBaseUrl = config('services.fedex.document_upload_base_url');
        $this->basicIntegratedVisibilityClientId = config('services.fedex.basic_integrated_visibility_client_id');
        $this->basicIntegratedVisibilityClientSecret = config('services.fedex.basic_integrated_visibility_client_secret');
    }

    /**
     * Get the OAuth access token from FedEx.
     *
     * @return string
     * @throws Exception
     */
    public function getAccessToken()
    {
        // if (Cache::has('fedex_access_token')) {
        //     return Cache::get('fedex_access_token');
        // }

        try {
            $response = Http::asForm()->post($this->baseUrl . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ]);

            if ($response->successful()) {
                $data = $response->json();

                \Log::info('FedEx Auth Response', $data);

                $token = $data['access_token'];
                // Default to 3600 seconds if not provided
                $expiresIn = $data['expires_in'] ?? 3600;

                // Cache the token, subtracting a buffer (e.g., 60 seconds) to ensure validity
                Cache::put('fedex_access_token', $token, $expiresIn - 60);

                return $token;
            }

            throw new Exception('FedEx Authentication Failed: ' . $response->body());
        } catch (Exception $e) {
            throw new Exception('FedEx Service Error: ' . $e->getMessage());
        }
    }

    /**
     * Test the connection by retreiving a token.
     *
     * @return array
     */
    public function testConnection()
    {
        try {
            $token = $this->getAccessToken();
            return [
                'success' => true,
                'message' => 'Successfully authenticated with FedEx.',
                'token' => substr($token, 0, 10) . '...' // Log only partial token for security
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    /**
     * Get rates from FedEx.
     *
     * @param array $recipientAddress
     * @param array $packages
     * @return array
     * @throws Exception
     */
    public function getRates($recipientAddress, $packages)
    {
        $token = $this->getAccessToken();

        $shipper = $this->getShipperAddress();

        \Log::error('FedEx Rate Shipper: ' . json_encode($shipper));

        // Recipient Address
        $streetLines = is_array($recipientAddress['address_lines'] ?? []) 
            ? $recipientAddress['address_lines'] 
            : [$recipientAddress['address_lines'] ?? ''];
            
        $streetLines = array_values(array_filter($streetLines));
        
        $postalCode = isset($recipientAddress['postal_code']) && !empty($recipientAddress['postal_code']) 
             ? $recipientAddress['postal_code'] 
             : '00000'; // Default if empty

        $requestCurrencyCode = strtoupper($recipientAddress['currency_code'] ?? 'KWD');

        $validatedRecipientAddress = $this->validateAddress($recipientAddress, $token);
        $recipientAddressForRate = $validatedRecipientAddress['address'] ?? $recipientAddress;
        $recipientResidential = $validatedRecipientAddress['residential'] ?? false;
        $streetLines = is_array($recipientAddressForRate['address_lines'] ?? [])
            ? $recipientAddressForRate['address_lines']
            : [$recipientAddressForRate['address_lines'] ?? ''];
        $streetLines = array_values(array_filter($streetLines));
        $postalCode = isset($recipientAddressForRate['postal_code']) && !empty($recipientAddressForRate['postal_code'])
            ? $recipientAddressForRate['postal_code']
            : $postalCode;

        $recipient = [
            'address' => [
                'city' => $recipientAddressForRate['city'] ?? '',
                'countryCode' => $recipientAddressForRate['country_code'] ?? 'KW',
                'postalCode' => $postalCode,
                'streetLines' => $streetLines,
                'residential' => $recipientResidential
            ]
        ];

        if (isset($recipientAddressForRate['postal_code']) && !empty($recipientAddressForRate['postal_code'])) {
            $recipient['address']['postalCode'] = $recipientAddressForRate['postal_code'];
        }

        if (!empty($recipientAddressForRate['state_or_province_code'])) {
            $recipient['address']['stateOrProvinceCode'] = $recipientAddressForRate['state_or_province_code'];
        }

        // Add contact details if available
        if (isset($recipientAddress['first_name']) || isset($recipientAddress['phone'])) {
            $recipient['contact'] = [
                'personName' => trim(($recipientAddress['first_name'] ?? '') . ' ' . ($recipientAddress['last_name'] ?? '')),
                'phoneNumber' => $recipientAddress['phone'] ?? '',
                'companyName' => $recipientAddress['company'] ?? ''
            ];
        }

        $requestedPackageLineItems = [];
        $commodities = [];
        $totalCustomsValue = 0;

        foreach ($packages as $pkg) {
            $qty = isset($pkg['count']) && $pkg['count'] > 1 ? (int) $pkg['count'] : 1;
            $weightVal = max((float) ($pkg['weight'] ?? 1), 0.1);
            $length = max((int) ($pkg['length'] ?? 10), 1);
            $width = max((int) ($pkg['width'] ?? 10), 1);
            $height = max((int) ($pkg['height'] ?? 10), 1);

            $item = [
                'groupPackageCount' => 1,
                'weight' => [
                    'units' => 'KG',
                    'value' => $weightVal
                ],
                'dimensions' => [
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'units' => 'CM'
                ]
            ];

            if ($qty > 1) {
                $item['groupPackageCount'] = $qty;
            }

            if (isset($pkg['price'])) {
                $item['declaredValue'] = [
                    'currency' => $requestCurrencyCode,
                    'amount' => (float)$pkg['price']
                ];
            }

            $requestedPackageLineItems[] = $item;

            // Commodity
            $price = isset($pkg['price']) ? (float)$pkg['price'] : 1.0;
            $description = $pkg['description'] ?? 'Item';
            if (strlen($description) > 400) $description = substr($description, 0, 400);

            $commodity = [
                'numberOfPieces' => $qty,
                'description' => $description,
                'countryOfManufacture' => 'KW', 
                'weight' => [
                    'units' => 'KG',
                    'value' => $weightVal * $qty
                ],
                'quantity' => $qty,
                'quantityUnits' => 'EA',
                'unitPrice' => [
                    'amount' => $price,
                    'currency' => $requestCurrencyCode
                ],
                'customsValue' => [
                    'amount' => $price * $qty,
                    'currency' => $requestCurrencyCode
                ]
            ];

            if (!empty($pkg['hs_code'])) {
                $commodity['harmonizedCode'] = $pkg['hs_code'];
            }

            $commodities[] = $commodity;
            $totalCustomsValue += ($price * $qty);
        }

        $shipperCountry = $shipper['address']['countryCode'] ?? 'KW';
        $recipientCountry = $recipient['address']['countryCode'] ?? 'KW';
        
        $customsClearanceDetail = null;
        if ($shipperCountry !== $recipientCountry) {
             $customsClearanceDetail = [
                'dutiesPayment' => [
                    'paymentType' => 'SENDER'
                ],
                'commodities' => $commodities,
                'totalCustomsValue' => [
                    'amount' => $totalCustomsValue,
                    'currency' => $requestCurrencyCode
                ]
            ];
        }

        $payload = [
            'accountNumber' => ['value' => $this->accountNumber],
            'rateRequestControlParameters' => [
                'rateSortOrder' => 'SERVICENAMETRADITIONAL',
                'returnTransitTimes' => true
            ],
            'requestedShipment' => [
                'shipper' => $shipper,
                'recipient' => $recipient,
                'shipDateStamp' => now()->addDay()->format('Y-m-d'), 
                'pickupType' => 'USE_SCHEDULED_PICKUP',
                'packagingType' => 'YOUR_PACKAGING',
                'rateRequestType' => ['ACCOUNT'],
                'requestedPackageLineItems' => $requestedPackageLineItems
            ]
        ];

        if ($customsClearanceDetail) {
             $payload['requestedShipment']['customsClearanceDetail'] = $customsClearanceDetail;
        }

        \Log::error('FedEx Rate Service Payload: ' . json_encode($payload));

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/rate/v1/rates/quotes', $payload);

            \Log::error('FedEx Rate Service Response: ' . json_encode($response->json()));

            if ($response->successful()) {
                return $response->json();
            }

            throw new FedExRateException(
                $this->extractFedexApiErrorMessage(
                    $response->json(),
                    $response->body(),
                    'FedEx could not return rates for the shipping address.'
                )
            );
        } catch (FedExRateException $e) {
            \Log::error('FedEx Rate Service Error: ' . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            \Log::error('FedEx Rate Service Error: ' . $e->getMessage());
            throw new Exception('FedEx Rate Service Error: ' . $e->getMessage());
        }
    }

    public function validateAddress(array $recipientAddress, ?string $token = null): array
    {
        $this->validateRequiredRecipientAddressForFedex($recipientAddress);

        $streetLines = is_array($recipientAddress['address_lines'] ?? [])
            ? $recipientAddress['address_lines']
            : [$recipientAddress['address_lines'] ?? ''];

        $streetLines = array_values(array_filter($streetLines));

        if (empty($streetLines) || empty($recipientAddress['country_code'])) {
            return [
                'address' => $recipientAddress,
                'residential' => false,
                'validated' => false,
            ];
        }

        $token = $token ?: $this->getAccessToken();

        $payload = [
            'addressesToValidate' => [
                [
                    'address' => [
                        'streetLines' => $streetLines,
                        'city' => $recipientAddress['city'] ?? '',
                        'stateOrProvinceCode' => $recipientAddress['state_or_province_code'] ?? '',
                        'postalCode' => $recipientAddress['postal_code'] ?? '',
                        'countryCode' => strtoupper($recipientAddress['country_code']),
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/address/v1/addresses/resolve', $payload);

            if (!$response->successful()) {
                throw new FedExAddressValidationException(
                    $this->extractFedexAddressValidationErrorMessage($response->json(), $response->body())
                );
            }

            $responseBody = $response->json();
            $resolvedAddress = $responseBody['output']['resolvedAddresses'][0] ?? null;

            if (!$resolvedAddress) {
                $message = $this->extractFedexAddressValidationErrorMessage(
                    $responseBody,
                    'FedEx could not validate the shipping address.'
                );

                Log::warning('FedEx Address Validation returned no resolved address.', [
                    'recipient' => $recipientAddress,
                    'response' => $responseBody,
                ]);

                throw new FedExAddressValidationException($message);
            }

            if ($this->hasBlockingAddressValidationIssue($resolvedAddress)) {
                throw new FedExAddressValidationException(
                    $this->extractFedexAddressValidationErrorMessage(
                        $responseBody,
                        'FedEx could not validate the shipping address.'
                    )
                );
            }

            $normalizedAddress = [
                'address_lines' => array_values(array_filter(
                    $resolvedAddress['streetLinesToken']
                        ?? $resolvedAddress['streetLines']
                        ?? $streetLines
                )),
                'city' => $resolvedAddress['city'] ?? ($recipientAddress['city'] ?? ''),
                'state_or_province_code' => $resolvedAddress['stateOrProvinceCode'] ?? ($recipientAddress['state_or_province_code'] ?? ''),
                'postal_code' => $resolvedAddress['postalCode'] ?? ($recipientAddress['postal_code'] ?? ''),
                'country_code' => strtoupper($resolvedAddress['countryCode'] ?? $recipientAddress['country_code']),
                'currency_code' => $recipientAddress['currency_code'] ?? 'KWD',
            ];

            $classification = strtoupper($resolvedAddress['classification'] ?? 'UNKNOWN');
            $isResidential = in_array($classification, ['RESIDENTIAL', 'MIXED'], true);

            Log::info('FedEx Address Validation resolved recipient address.', [
                'input' => $recipientAddress,
                'resolved' => $normalizedAddress,
                'classification' => $classification,
            ]);

            return [
                'address' => $normalizedAddress,
                'residential' => $isResidential,
                'validated' => true,
                'response' => $responseBody,
            ];
        } catch (FedExAddressValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::warning('FedEx Address Validation skipped, using original recipient address.', [
                'recipient' => $recipientAddress,
                'error' => $e->getMessage(),
            ]);

            throw new FedExAddressValidationException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    private function validateRequiredRecipientAddressForFedex(array $recipientAddress): void
    {
        $streetLines = is_array($recipientAddress['address_lines'] ?? [])
            ? $recipientAddress['address_lines']
            : [$recipientAddress['address_lines'] ?? ''];
        $streetLines = array_values(array_filter(array_map('trim', $streetLines)));

        $missingFields = [];

        if (empty($streetLines)) {
            $missingFields[] = 'street address';
        }

        if (empty(trim((string) ($recipientAddress['city'] ?? '')))) {
            $missingFields[] = 'city';
        }

        if (empty(trim((string) ($recipientAddress['postal_code'] ?? '')))) {
            $missingFields[] = 'postal code';
        }

        if (empty(trim((string) ($recipientAddress['country_code'] ?? '')))) {
            $missingFields[] = 'country';
        }

        if (empty($missingFields)) {
            return;
        }

        throw new FedExAddressValidationException(
            'Please complete your shipping address before continuing. Missing: ' . implode(', ', $missingFields) . '.'
        );
    }

    private function extractFedexAddressValidationErrorMessage($responseBody, string $fallbackMessage): string
    {
        $message = $this->extractFedexApiErrorMessage(
            $responseBody,
            $fallbackMessage,
            $fallbackMessage,
            true
        );

        return $this->getFriendlyAddressErrorMessage($message, $fallbackMessage);
    }

    private function extractFedexApiErrorMessage($responseBody, string $rawFallbackMessage, string $fallbackMessage, bool $includeResolvedAddressAttributes = false): string
    {
        if (!is_array($responseBody)) {
            return $rawFallbackMessage ?: $fallbackMessage;
        }

        $messages = [];

        foreach (($responseBody['errors'] ?? []) as $error) {
            $message = trim((string) ($error['message'] ?? ''));
            if ($message !== '') {
                $messages[] = $message;
            }
        }

        foreach (($responseBody['output']['alerts'] ?? []) as $alert) {
            $message = trim((string) ($alert['alert'] ?? $alert['message'] ?? ''));
            if ($message !== '') {
                $messages[] = $message;
            }
        }

        if ($includeResolvedAddressAttributes) {
            foreach (($responseBody['output']['resolvedAddresses'][0]['attributes'] ?? []) as $attribute) {
                $attributeCode = strtoupper((string) ($attribute['name'] ?? $attribute['attributeType'] ?? ''));
                $attributeValue = strtoupper((string) ($attribute['value'] ?? ''));

                if (in_array($attributeCode, ['INVALID', 'INTERPOLATED.STREET.ADDRESS', 'MISSING_OR_AMBIGUOUS'], true)
                    || str_contains($attributeValue, 'FALSE')) {
                    $messages[] = trim((string) ($attribute['message'] ?? $attributeCode));
                }
            }
        }

        $messages = array_values(array_unique(array_filter($messages)));

        $message = !empty($messages) ? implode(' ', $messages) : $fallbackMessage;

        return $this->getFriendlyAddressErrorMessage($message, $fallbackMessage);
    }

    private function getFriendlyAddressErrorMessage(string $message, string $fallbackMessage): string
    {
        $normalizedMessage = strtolower(trim($message));

        if ($normalizedMessage === '') {
            return $fallbackMessage;
        }

        $addressRelatedPatterns = [
            'invalid service and packaging combination',
            'service is not available',
            'service is invalid',
            'destination postal code',
            'postal code',
            'postcode',
            'zip code',
            'city',
            'state',
            'province',
            'recipient address',
            'delivery address',
            'address is invalid',
            'address could not be resolved',
            'missing or ambiguous',
            'invalid address',
        ];

        foreach ($addressRelatedPatterns as $pattern) {
            if (str_contains($normalizedMessage, $pattern)) {
                return 'Please check your shipping address. The city, postal code, or address details appear to be invalid for delivery.';
            }
        }

        return $message;
    }

    private function hasBlockingAddressValidationIssue(array $resolvedAddress): bool
    {
        foreach (($resolvedAddress['attributes'] ?? []) as $attribute) {
            $attributeCode = strtoupper((string) ($attribute['name'] ?? $attribute['attributeType'] ?? ''));
            $attributeValue = strtoupper((string) ($attribute['value'] ?? ''));

            if (in_array($attributeCode, ['INVALID', 'INTERPOLATED.STREET.ADDRESS', 'MISSING_OR_AMBIGUOUS'], true)) {
                return true;
            }

            if (in_array($attributeValue, ['FALSE', 'UNAVAILABLE'], true)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Helper to get Shipper Address from Warehouse Configuration
     */
    public function getShipperAddress()
    {
        $warehouse = WarehouseAddress::getAddress();
        if (!$warehouse) {
            $warehouse = WarehouseAddress::first(); // Fallback
        }
        
        // Defaults
        $city = 'Kuwait City';
        $countryCode = 'KW';
        $streetLines = ['Store Address'];
        $postalCode = '';
        $phone = '';

        if ($warehouse) {
            $cityId = $warehouse->area ?? $city;

            if($cityId) {
                $cityDesc = AdminCityDescription::where('city_id', $cityId)
                ->where('language_id', 1)
                ->first();
                if ($cityDesc) {
                    $city = $cityDesc->city_name;
                }
            }
            
            if ($warehouse->country_id) {
                $country = AdminCountry::find($warehouse->country_id);
                if ($country) {
                    $countryCode = $country->country_code;
                }
            }
            
            // Build FedEx-safe 2-line street format
            $line1Parts = array_filter([
                $warehouse->street,
                $warehouse->block ? $warehouse->block : null,
            ]);

            $line2Parts = array_filter([
                $warehouse->number,
                $warehouse->address_detail
            ]);

            $streetLines = [];

            if (!empty($line1Parts)) {
                $streetLines[] = implode(', ', $line1Parts);
            }

            if (!empty($line2Parts)) {
                $streetLines[] = implode(', ', $line2Parts);
            }

            if (empty($streetLines)) {
                $streetLines = ['Warehouse Address'];
            }

            $postalCode = $warehouse->pin_code ?? '';
            $phone = $warehouse->phone ?? '';
        }

        return [
            'address' => [
                'city' => $city,
                'countryCode' => $countryCode,
                'postalCode' => $postalCode,
                'streetLines' => $streetLines
            ],
            'contact' => [
                'phoneNumber' => $phone,
                'companyName' => config('app.name', 'Reehana')
            ]
        ];
    }

    /**
     * Create a Pickup Request
     */
    public function createPickupRequest($pickupDetails)
    {
        $token = $this->getAccessToken();
        
        $shipperAddress = $this->getShipperAddress();

        if (app()->environment('local', 'sandbox')) {
            $payload = [
                'associatedAccountNumber' => [
                    'value' => $this->accountNumber
                ],
                'originDetail' => [
                    'pickupLocation' => [
                        'contact' => [
                            'personName'  => 'Warehouse Manager',
                            'phoneNumber' => $shipperAddress['contact']['phoneNumber']
                        ],
                        'address' => [
                            'streetLines' => $shipperAddress['address']['streetLines'],
                            'city' => $shipperAddress['address']['city'],
                            'stateOrProvinceCode' => 'KU', // ðŸ”¥ REQUIRED even in KW
                            'postalCode' => $shipperAddress['address']['postalCode'] ?? '00000',
                            'countryCode' => $shipperAddress['address']['countryCode']
                        ]
                    ],

                    'readyDateTimestamp' => now()->addHours(2)->format('Y-m-d\TH:i:s'),
                    'customerCloseTime' => '18:00:00'
                ],
                'carrierCode' => 'FDXE',
            ];
        }
        else {
            $payload = [
                'associatedAccountNumber' => [
                    'value' => $this->accountNumber
                ],
                'originDetail' => [
                    'pickupLocation' => [
                        'contact' => [
                            'personName'  => 'Warehouse Manager',
                            'phoneNumber' => $shipperAddress['contact']['phoneNumber']
                        ],
                        'address' => [
                            'streetLines' => $shipperAddress['address']['streetLines'],
                            'city' => $shipperAddress['address']['city'],
                            // 'stateOrProvinceCode' => 'KU', // ðŸ”¥ REQUIRED even in KW
                            'postalCode' => $shipperAddress['address']['postalCode'] ?? '00000',
                            'countryCode' => $shipperAddress['address']['countryCode']
                        ]
                    ],

                    'packageLocation' => 'FRONT',
                    'buildingPartDescription' => 'RECEPTION',

                    'packageCount' => $pickupDetails['packageCount'],

                    'countryRelationships' => [
                        'originCountryCode' => 'KW',
                        'destinationCountryCode' => 'KW'
                    ],

                    'totalWeight' => [
                        'units' => $pickupDetails['totalWeight']['units'] ?? 'KG',
                        'value' => $pickupDetails['totalWeight']['value']
                    ],

                    'readyDateTimestamp' => now()->addHours(2)->format('Y-m-d\TH:i:s'),
                    'customerCloseTime' => '18:00:00'
                ],
                'carrierCode' => 'FDXE',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/pickup/v1/pickups', $payload);

            if ($response->successful()) {
                return $response->json();
            }
            throw new Exception('FedEx Pickup Error: ' . $response->body());
        } catch (Exception $e) {
            throw new Exception('FedEx Pickup Service Error: ' . $e->getMessage());
        }
    }

    /**
     * Create a Shipment
     */
    public function createShipment($shipmentDetails)
    {
        $token = $this->getAccessToken();
        
        // Merge default shipper if not provided
        if (!isset($shipmentDetails['requestedShipment']['shipper'])) {
            $shipper = $this->getShipperAddress();
            $shipmentDetails['requestedShipment']['shipper'] = $shipper;
        }

        // Extract recipient country safely
        $recipientCountry = $shipmentDetails['requestedShipment']['recipients']['address']['countryCode'] ?? 'KW';
        // $shipperCountry   = $shipper['address']['countryCode'] ?? 'KW';

        $shipper = $shipmentDetails['requestedShipment']['shipper'] ?? $this->getShipperAddress();
        $shipperCountry = $shipper['address']['countryCode'] ?? 'KW';
        
        $customsClearanceDetail = null;

        if ($recipientCountry && $shipperCountry !== $recipientCountry) {

            $commodities = [];
            $totalCustomsValue = 0;

            if (!empty($shipmentDetails['requestedShipment']['commodities'])) {

                foreach ($shipmentDetails['requestedShipment']['commodities'] as $item) {

                    $price = (float) ($item['price'] ?? 1);
                    $qty   = (int) ($item['quantity'] ?? 1);

                    $commodity = [
                        'numberOfPieces' => $qty,
                        'description' => substr($item['description'] ?? 'Item', 0, 400),
                        'countryOfManufacture' => $item['countryOfManufacture'] ?? 'KW',
                        'weight' => [
                            'units' => strtoupper($item['weight_unit']) ?? 'KG',
                            'value' => $item['weight'] ?? 1
                        ],
                        'quantity' => $qty,
                        'quantityUnits' => 'EA',
                        'unitPrice' => [
                            'amount' => $price,
                            'currency' => $item['currency'] ?? 'USD'
                        ],
                        'customsValue' => [
                            'amount' => $price * $qty,
                            'currency' => $item['currency'] ?? 'USD'
                        ]
                    ];

                    if (!empty($item['harmonizedCode'])) {
                        $commodity['harmonizedCode'] = $item['harmonizedCode'];
                    }

                    $commodities[] = $commodity;

                    $totalCustomsValue += ($price * $qty);
                }
            }

            $customsClearanceDetail = [
                'dutiesPayment' => [
                    'paymentType' => 'SENDER'
                ],
                'commodities' => $commodities,
                'totalCustomsValue' => [
                    'amount' => $totalCustomsValue,
                    'currency' => $shipmentDetails['requestedShipment']['commodities'][0]['currency'] ?? 'USD'
                ]
            ];
        }

        $basePayload = [
            'labelResponseOptions' => 'LABEL',
            'accountNumber' => [
                'value' => $this->accountNumber
            ],
            'requestedShipment' => [
                'shipper' => $this->getShipperAddress(),

                'pickupType' => 'USE_SCHEDULED_PICKUP',
                'serviceType' => 'INTERNATIONAL_PRIORITY',
                'packagingType' => 'YOUR_PACKAGING',

                'shippingChargesPayment' => [
                    'paymentType' => 'SENDER',
                    'payor' => [
                        'responsibleParty' => [
                            'accountNumber' => [
                                'value' => $this->accountNumber
                            ]
                        ]
                    ]
                ],

                'labelSpecification' => [
                    'imageType' => 'PDF',
                    'labelStockType' => 'PAPER_85X11_TOP_HALF_LABEL'
                ]
            ]
        ];

        if ($customsClearanceDetail) {
            $basePayload['requestedShipment']['customsClearanceDetail'] = $customsClearanceDetail;
        }

        if ($recipientCountry && $shipperCountry !== $recipientCountry) {
        //     // ✅ CORRECTED ETD IMPLEMENTATION
        //     // Based on documentation, POST_SHIPMENT_UPLOAD_REQUESTED belongs in shipmentSpecialServices -> etdDetail
        //     $basePayload['requestedShipment']['shipmentSpecialServices'] = [
        //         'etdDetail' => [
        //             'attributes' => ['POST_SHIPMENT_UPLOAD_REQUESTED']
        //         ]
        //     ];
            
        //     // Note: We are NOT adding shippingDocumentSpecification['shippingDocumentTypes'] => ['COMMERCIAL_INVOICE']
        //     // because we are uploading the invoice manually (POST_SHIPMENT_UPLOAD_REQUESTED).
        //     // Requesting generation would require setting correct image types (PDF) to avoid errors.

            $basePayload['requestedShipment']['shipmentSpecialServices'] = [
                'specialServiceTypes' => ['ELECTRONIC_TRADE_DOCUMENTS'],
                'etdDetail' => [
                    'attributes' => ['POST_SHIPMENT_UPLOAD_REQUESTED']
                ]
            ];
        }
        
        // Force recipients array
        if (isset($shipmentDetails['requestedShipment']['recipients'])
            && !isset($shipmentDetails['requestedShipment']['recipients'][0])) {
        
            $shipmentDetails['requestedShipment']['recipients'] = [
                $shipmentDetails['requestedShipment']['recipients']
            ];
        }
        
        // Fix streetLines
        $recipient = &$shipmentDetails['requestedShipment']['recipients'][0];
        
        if (isset($recipient['address']['streetLines'])) {
            $lines = array_values(array_filter($recipient['address']['streetLines']));
            if (empty($lines)) {
                $lines = ['N/A'];
            }
            if (strlen(trim($lines[0])) < 3) {
                $lines[0] = str_pad(trim($lines[0]), 3, '-', STR_PAD_RIGHT);
            }
            $recipient['address']['streetLines'] = $lines;
        }

        if (empty($recipient['address']['stateOrProvinceCode'])) {
            // Attempt to fetch the state via Address Validation
            try {
                $validatedData = $this->validateAddress([
                    'city' => $recipient['address']['city'] ?? '',
                    'country_code' => $recipient['address']['countryCode'] ?? '',
                    'state_or_province_code' => '',
                    'postal_code' => $recipient['address']['postalCode'] ?? '',
                    'address_lines' => $recipient['address']['streetLines'] ?? []
                ], $token);
                
                if (!empty($validatedData['address']['state_or_province_code'])) {
                    $recipient['address']['stateOrProvinceCode'] = $validatedData['address']['state_or_province_code'];
                }
                if (!empty($validatedData['address']['postal_code'])) {
                    $recipient['address']['postalCode'] = $validatedData['address']['postal_code'];
                }
            } catch (\Exception $e) {
                \Log::warning('Address Validation failed before shipping: ' . $e->getMessage());
            }

            if (empty($recipient['address']['stateOrProvinceCode'])) {
                if (($recipient['address']['countryCode'] ?? 'KW') === 'KW') {
                    $recipient['address']['stateOrProvinceCode'] = 'KU';
                } else {
                    unset($recipient['address']['stateOrProvinceCode']);
                }
            }
        }
        
        unset($shipmentDetails['requestedShipment']['commodities']);

        // ðŸ”¥ IMPORTANT: use recursive replace
        // $payload = array_replace_recursive($basePayload, $shipmentDetails);
        $payload = $basePayload;

        // Manually set only allowed fields
        $payload['requestedShipment']['recipients'] =
            $shipmentDetails['requestedShipment']['recipients'];
        
        $cleanPackages = [];

        foreach ($shipmentDetails['requestedShipment']['requestedPackageLineItems'] as $pkg) {
        
            $cleanPackages[] = [
                'groupPackageCount' => (int) ($pkg['quantity'] ?? 1),
                'weight' => [
                    'units' => 'KG',
                    'value' => (float) $pkg['weight']['value']
                ],
                'dimensions' => [
                    'length' => (int) $pkg['dimensions']['length'],
                    'width'  => (int) $pkg['dimensions']['width'],
                    'height' => (int) $pkg['dimensions']['height'],
                    'units'  => 'CM'
                ]
            ];
        }
        
        $payload['requestedShipment']['requestedPackageLineItems'] = $cleanPackages;


        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/ship/v1/shipments', $payload);

            \Log::info('FedEx Shipment Service Payload: ' . json_encode($payload));

            if ($response->successful()) {

                \Log::info('Tracking number saved:', [
                    'tracking' => $response->json()
                ]);

                return $response->json();
            }
            throw new Exception('FedEx Ship Error: ' . $response->body());
        } catch (Exception $e) {
            throw new Exception('FedEx Ship Service Error: ' . $e->getMessage());
        }
    }

    /**
     * Upload Trade Documents
     * 
     * @param array $files Array of ['name', 'contents', 'contentType']
     * @param array $metaData Array of ['workflowName', 'carrierCode', 'name', 'contentType']
     */
    public function uploadTradeDocument($trackingNumber, $fileName, $base64Content)
    {
        $token = $this->getAccessToken();

        $url = rtrim($this->baseUrl, '/') . '/documents/v1/etds/upload';

        $payload = [
            'document' => [
                'documentType'   => 'COMMERCIAL_INVOICE',
                'trackingNumber' => $trackingNumber,
                'shipmentDate'   => now()->format('Y-m-d'), // VERY IMPORTANT
                'fileName'       => $fileName,
                'contentType'    => 'application/pdf',
                'content'        => $base64Content
            ]
        ];

        \Log::info('Uploading ETD for tracking:', [
            'tracking' => $trackingNumber
        ]);


        $response = Http::withToken($token)
            ->acceptJson()
            ->post($url, $payload);


            \Log::info('FedEx Document Upload Response: ' . json_encode($response));

        //     dd($response->json());

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }

    public function getBasicIntegratedVisibilityAccessToken()
    {
        if (Cache::has('fedex_basic_integrated_visibility_access_token')) {
            return Cache::get('fedex_basic_integrated_visibility_access_token');
        }

        try {
            $response = Http::asForm()->post($this->baseUrl . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->basicIntegratedVisibilityClientId,
                'client_secret' => $this->basicIntegratedVisibilityClientSecret
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'];
                // Default to 3600 seconds if not provided
                $expiresIn = $data['expires_in'] ?? 3600;

                // Cache the token, subtracting a buffer (e.g., 60 seconds) to ensure validity
                Cache::put('fedex_basic_integrated_visibility_access_token', $token, $expiresIn - 60);

                return $token;
            }

            throw new Exception('FedEx Authentication Failed: ' . $response->body());
        } catch (Exception $e) {
            throw new Exception('FedEx Service Error: ' . $e->getMessage());
        }
    }


    /**
     * Track Status (Shipment or Pickup)
     * 
     * @param string $trackingNumber
     * @return array
    */
    public function trackStatus($trackingNumber)
    {
        $token = $this->getBasicIntegratedVisibilityAccessToken();
        
        $payload = [
            'trackingInfo' => [
                [
                    'trackingNumberInfo' => [
                        'trackingNumber' => $trackingNumber
                    ]
                ]
            ],
            'includeDetailedScans' => true
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/track/v1/trackingnumbers', $payload);

            if ($response->successful()) {
                return $response->json();
            }
            throw new Exception('FedEx Tracking Error: ' . $response->body());
        } catch (Exception $e) {
            throw new Exception('FedEx Tracking Service Error: ' . $e->getMessage());
        }
    }
}
