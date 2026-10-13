<?php
namespace App\Services;

use App\AdminModel\AdminCountry;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\City\AdminCityDescription;
use App\Helpers\Helper;

use App\Services\FedExService;

class ShippingService
{
    private const FEDEX_TRANSIENT_ERROR_CODES = [
        'SYSTEM.UNAVAILABLE.EXCEPTION',
        'SYSTEM.UNEXPECTED.ERROR',
    ];
    private const FEDEX_RATE_MAX_RETRIES = 2;
    private const BASE_CURRENCY_CODE = 'KWD';
    private const DEFAULT_CURRENCY_RATE = 1.0;

    private $currencyRateByCodeCache = [];

    public function calculateFedexShipping($address, $cart)
    {
        $totalShipping = 0;
        $isPickup = false;
        $package = [];
        $productWeights = [];
        $totalWeight = 0;
        $productShipping = [];

        // Determine if Pickup
        if ($address && isset($address->pickupstore) && $address->pickupstore == 1) {
            $isPickup = true;
        }

        // Determine Country
        $countryObj = null;
        if ($address) {
            $countryObj = AdminCountry::find($address->country_id);
        } else {
            $countryObj = AdminCountry::where('is_default', 1)->first();
        }

        $countryCode = $countryObj->country_code ?? 'KW';
        $selectedCurrency = Helper::getDefaultCurrency();
        $selectedCurrencyCode = strtoupper($selectedCurrency->code ?? $selectedCurrency->currency_code ?? self::BASE_CURRENCY_CODE);
        $selectedCurrencyRate = (float)($selectedCurrency->value ?? self::DEFAULT_CURRENCY_RATE);
        if ($selectedCurrencyRate <= 0) {
            $selectedCurrencyRate = self::DEFAULT_CURRENCY_RATE;
        }
        $recipientCurrencyCode = strtoupper($countryObj->currency_code ?? $selectedCurrencyCode);

        $isCurrencyKwd = ($selectedCurrencyCode === 'KWD');
        $isShippingCountryKuwait = $address && strtoupper($countryCode) === 'KW';
        $skipFedexRateCall = $isCurrencyKwd && $isShippingCountryKuwait;

        $cityName = $address->city ?? '';
        if ($address) {
            $cityDesc = AdminCityDescription::where('city_id', $address->city_id)
                ->where('language_id', 1)
                ->first();
            if ($cityDesc) {
                $cityName = $cityDesc->city_name;
            }
        }

        $recipientParams = [
            'city' => $cityName,
            'country_code' => $countryCode,
            'currency_code' => $recipientCurrencyCode,
            'state_or_province_code' => $address ? ($address->state_or_province_code ?? '') : '',
            'postal_code' => $address ? ($address->pin_code ?? '') : '',
            'address_lines' => $address ? array_filter([
                // $address->address_title ?? '',
                $address->block ?? '',
                $address->street ?? '',
                $address->house_number ?? '',
                $address->address_detail ?? ''
            ]) : []
        ];

        // Process Cart Items
        foreach ($cart as $record) {
             // In CartController, intlShipFlag check was done. 
             // Here we assume all items in cart are valid for shipping or we process them.
             // But weights accumulate.
            
            $product = AdminProduct::find($record->product_id);
            if (!$product) continue;

            $sizeId = $record->size_id;
            $colorId = $record->color_id;
            $product_weight = $product->product_weight;
            $product_length = (int)($product->product_length ?? 10);
            $product_width = (int)($product->product_width ?? 10);
            $product_height = (int)($product->product_height ?? 10);
             
             // Check attributes for weight override
            if ($sizeId || $colorId) {
                $attributePrice = $product->getProductAttributeProductPage($product->id, $sizeId, $colorId);

                if ($attributePrice) {
                    $product_weight = $attributePrice->weight;
                    $product_length = (int)($attributePrice->product_length ?? $product_length);
                    $product_width = (int)($attributePrice->product_width ?? $product_width);
                    $product_height = (int)($attributePrice->product_height ?? $product_height);
                }
            }

            $weight = ($product_weight > 0 ? $product_weight : 0.5) * $record->quantity;

            if (!$isPickup) {
                $hsCode = $product->getHsCode($sizeId, $colorId);

                $package[] = [
                    'weight' => $weight,
                    'length' => (int)($product_length ?? 10),
                    'width'  => (int)($product_width ?? 10),
                    'height' => (int)($product_height ?? 10),
                    'count'  => (int)$record->quantity,
                    'description' => $product->product_title_eng ?? 'Item',
                    'hs_code' => $hsCode
                ];
                
                $productWeights[$record->product_id] = $weight;
                $totalWeight += $weight;
            }
        }

        // 1. Try FedEx
        $fedexRateFound = false;
        if ($address && !$isPickup && !empty($package) && $address->id != 43 && $address->id != 7 && !$skipFedexRateCall) {
            try {
                $fedexService = new FedExService();
                $rates = $this->getFedexRatesWithRetry($fedexService, $recipientParams, $package);

                if (isset($rates['output']['rateReplyDetails'][0]['ratedShipmentDetails'][0]['totalNetCharge'])) {
                    $fedexCharge = $rates['output']['rateReplyDetails'][0]['ratedShipmentDetails'][0]['totalNetCharge'];
                    $normalizedShipping = $this->normalizeFedexChargeToSelectedCurrency(
                        $fedexCharge,
                        $selectedCurrencyCode,
                        $selectedCurrencyRate
                    );

                    if ($normalizedShipping !== null) {
                        $totalShipping = $normalizedShipping;
                        $fedexRateFound = true;
                    } else {
                        \Log::warning('FedEx charge conversion skipped, falling back to local shipping.', [
                            'selected_currency' => $selectedCurrencyCode,
                            'fedex_charge' => $fedexCharge
                        ]);
                    }
                }
            } catch (\App\Exceptions\FedExAddressValidationException $e) {
                // Let validation errors bubble up immediately to the checkout flow
                throw $e;
            } catch (\App\Exceptions\FedExRateException $e) {
                // Handled in CartController/CheckoutController directly
                throw $e;
            } catch (\Exception $e) {
                \Log::error('FedEx Shipping Error: ' . $e->getMessage());
            }
        }
        
        // 2. Fallback (Weight Based)
        if (!$fedexRateFound && !$isPickup) {
             // Special Address Logic
             if ($address && $address->id == 7) {
                 $totalShipping = 0;
             } else {
                 // Calculate based on Country/Zone
                 $shipping_charge = 0;
                 if ($countryObj) {
                     $shipping_charge = $countryObj->shipping_charge;
                     
                     // Zone Check (Only if address exists)
                     if ($address && $address->id != '7') {
                        $zoneprice = Helper::getZonePrice($address->city_id);
                        if ($zoneprice > 0) {
                            $shipping_charge = $zoneprice;
                        }
                     }
                     
                     // Currency Conversion
                     $currency = Helper::getDefaultCurrency();
                     if ($currency) {
                         $shipping_charge = $shipping_charge * $currency->value;
                     }
                    //  dd("hu", $shipping_charge, $currency->value);
                     
                     // Weight Rounding Logic
                     $roundweight = ceil($totalWeight);
                     $countryName = $countryObj->country_name ?? 'Kuwait'; // Use English name typically? 
                     // Or fetch description like original code:
                     // $countryName = \App\AdminModel\AdminCountryDescription::where('country_id', $address->country_id)->first()->country_name;
                     // Lets approximate "Kuwait" check.
                     // The original code checked strtolower($countryName) == 'kuwait'.
                     // Assuming ID 1 or 2 is Kuwait? Code defaults to 'kuwait'.
                     
                     // Helper::getCountry(1) is Kuwait?
                     // Let's use logic: if weight < 1 make it 1.
                     if ($roundweight < 1) {
                         $roundweight = 1;
                     }
                     
                     // Original logic: if country is Kuwait, roundweight is always at least 1?
                     // "if ($roundweight < 1 || strtolower($countryName) =='kuwait' ) { $roundweight = 1; }"
                     // This actually sets roundweight to 1 if it is Kuwait, regardless of total weight? 
                     // No, it says if weight < 1 OR country is kuwait... wait.
                     // If country is Kuwait, roundweight = 1. ALWAYS? That seems buggy in original code or intended for flat rate per kg?
                     // Ah, "Round up to 1 if < 1 OR if Kuwait". 
                     // If I have 5kg in Kuwait, does it become 1? Yes according to that logic.
                     // "if ... || ... { $roundweight = 1; }"
                     // That implies flat shipping for Kuwait?
                     // Let's preserve strictly:
                     
                     // Recover Country Name for logic
                     $cName = 'kuwait';
                     if ($address) {
                         $desc = \App\AdminModel\AdminCountryDescription::where('country_id', $address->country_id)->first();
                         if ($desc) $cName = $desc->country_name;
                     } else {
                         // Default country
                         if($countryObj) {
                              $desc = \App\AdminModel\AdminCountryDescription::where('country_id', $countryObj->id)->first();
                              if ($desc) $cName = $desc->country_name;
                         }
                     }
                     
                     if ($roundweight < 1 || strtolower($cName) == 'kuwait') {
                         $roundweight = 1;
                     }
                     
                     $totalShipping = $shipping_charge * $roundweight;
                 }
             }
        }

        // 🔥 Split shipping product-wise based on weight
        if ($totalShipping > 0 && $totalWeight > 0) {

            foreach ($productWeights as $productId => $weight) {

                $ratio = $weight / $totalWeight;

                $productShipping[$productId] = Helper::formatNumber(
                    $totalShipping * $ratio
                );
            }

        } else {

            // If pickup or free shipping
            foreach ($productWeights as $productId => $weight) {
                $productShipping[$productId] = 0;
            }
        }

        return [
            'totalShipping'   => Helper::formatNumber($totalShipping),
            'productShipping' => $productShipping // Note: Product shipping split logic is skipped for fallback for now to keep it simple, or we can add it if needed.
        ];
    }

    private function getFedexRatesWithRetry(FedExService $fedexService, array $recipientParams, array $package): array
    {
        $attempt = 0;
        $maxAttempts = self::FEDEX_RATE_MAX_RETRIES + 1;

        while ($attempt < $maxAttempts) {
            try {
                return $fedexService->getRates($recipientParams, $package);
            } catch (\Exception $e) {
                $attempt++;
                $matchedTransientErrorCode = $this->getMatchedTransientFedexErrorCode($e->getMessage());
                $isTransientFedexError = $matchedTransientErrorCode !== null;
                $shouldRetry = $isTransientFedexError && $attempt < $maxAttempts;

                if (!$shouldRetry) {
                    throw $e;
                }

                \Log::warning('FedEx rate retrying after transient error', [
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'error_code' => $matchedTransientErrorCode,
                ]);

                usleep($attempt * 500000); // 0.5s, then 1.0s
            }
        }

        throw new \Exception('FedEx rate retries exhausted.');
    }

    private function getMatchedTransientFedexErrorCode(string $message): ?string
    {
        foreach (self::FEDEX_TRANSIENT_ERROR_CODES as $errorCode) {
            if (strpos($message, $errorCode) !== false) {
                return $errorCode;
            }
        }

        return null;
    }

    private function normalizeFedexChargeToSelectedCurrency($fedexCharge, string $selectedCurrencyCode, float $selectedCurrencyRate): ?float
    {
        $chargeAmount = 0.0;
        $chargeCurrencyCode = $selectedCurrencyCode;

        if (is_array($fedexCharge)) {
            $chargeAmount = (float)($fedexCharge['amount'] ?? 0);
            $chargeCurrencyCode = strtoupper(
                $fedexCharge['currency']
                    ?? $fedexCharge['currencyCode']
                    ?? $selectedCurrencyCode
            );
        } else {
            $chargeAmount = (float)$fedexCharge;
        }

        if ($chargeAmount <= 0) {
            return 0.0;
        }

        if ($chargeCurrencyCode === $selectedCurrencyCode) {
            return $chargeAmount;
        }

        $chargeCurrencyRate = $this->getCurrencyRateByCode($chargeCurrencyCode);
        if ($chargeCurrencyRate <= 0 || $selectedCurrencyRate <= 0) {
            return null;
        }

        $amountInBaseCurrency = $chargeAmount / $chargeCurrencyRate;
        return $amountInBaseCurrency * $selectedCurrencyRate;
    }

    private function getCurrencyRateByCode(string $currencyCode): float
    {
        $currencyCode = strtoupper(trim($currencyCode));

        if ($currencyCode === '' || $currencyCode === self::BASE_CURRENCY_CODE) {
            return self::DEFAULT_CURRENCY_RATE;
        }

        if (isset($this->currencyRateByCodeCache[$currencyCode])) {
            return $this->currencyRateByCodeCache[$currencyCode];
        }

        $country = AdminCountry::whereRaw('UPPER(currency_code) = ?', [$currencyCode])->first();
        $rate = (float)($country->currency_rate ?? 0);
        $this->currencyRateByCodeCache[$currencyCode] = $rate;

        return $rate;
    }

    public function getShippingWithCache($address, $cart, $userId = null)
    {
        // \App\Helpers\Helper::removeShippingCache($address, $cart, $userId); // Clear cache if needed before fetching
        $cacheKey = \App\Helpers\Helper::getShippingCacheKey($address, $cart, $userId);

        $shippingData = \Cache::get($cacheKey);

        if (!$shippingData) {

            $shippingData = $this->calculateFedexShipping($address, $cart);

            \Cache::put($cacheKey, $shippingData, now()->addMinutes(30));
        }

        return $shippingData;
    }
}
