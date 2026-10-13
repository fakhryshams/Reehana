<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ImportCountries extends Command
{
    protected $signature = 'countries:import';
    protected $description = 'Import countries with live currency rate (KWD base)';

    public function handle()
    {
        $this->info("Fetching exchange rates...");

        $apiKey = env('EXCHANGE_API_KEY');
        $response = Http::get("https://v6.exchangerate-api.com/v6/$apiKey/latest/KWD");

        if (!$response->successful()) {
            $this->error("API failed");
            return;
        }

        $rates = $response->json()['conversion_rates'];

        // 🔥 Tamari countries list (short example – tame full muki sako)
        $countries = [
        ['name'=>'Afghanistan','code'=>'AF','currency'=>'AFN'],
        ['name'=>'Albania','code'=>'AL','currency'=>'ALL'],
        ['name'=>'Algeria','code'=>'DZ','currency'=>'DZD'],
        ['name'=>'American Samoa','code'=>'AS','currency'=>'USD'],
        ['name'=>'Andorra','code'=>'AD','currency'=>'EUR'],
        ['name'=>'Angola','code'=>'AO','currency'=>'AOA'],
        ['name'=>'Anguilla','code'=>'AI','currency'=>'XCD'],
        ['name'=>'Antigua and Barbuda','code'=>'AG','currency'=>'XCD'],
        ['name'=>'Argentina','code'=>'AR','currency'=>'ARS'],
        ['name'=>'Armenia','code'=>'AM','currency'=>'AMD'],
        ['name'=>'Aruba','code'=>'AW','currency'=>'AWG'],
        ['name'=>'Australia','code'=>'AU','currency'=>'AUD'],
        ['name'=>'Austria','code'=>'AT','currency'=>'EUR'],
        ['name'=>'Azerbaijan','code'=>'AZ','currency'=>'AZN'],
        ['name'=>'Bahamas','code'=>'BS','currency'=>'BSD'],
        ['name'=>'Bangladesh','code'=>'BD','currency'=>'BDT'],
        ['name'=>'Barbados','code'=>'BB','currency'=>'BBD'],
        ['name'=>'Belarus','code'=>'BY','currency'=>'BYR'],
        ['name'=>'Belgium','code'=>'BE','currency'=>'EUR'],
        ['name'=>'Belize','code'=>'BZ','currency'=>'BZD'],
        ['name'=>'Benin','code'=>'BJ','currency'=>'XOF'],
        ['name'=>'Bermuda','code'=>'BM','currency'=>'BMD'],
        ['name'=>'Bhutan','code'=>'BT','currency'=>'INR'],
        ['name'=>'Bolivia','code'=>'BO','currency'=>'BOB'],
        ['name'=>'Bonaire','code'=>'BQ','currency'=>'USD'],
        ['name'=>'Bosnia and Herzegovina','code'=>'BA','currency'=>'BAM'],
        ['name'=>'Botswana','code'=>'BW','currency'=>'BWP'],
        ['name'=>'Brazil','code'=>'BR','currency'=>'BRL'],
        ['name'=>'British Indian Ocean Territory','code'=>'IO','currency'=>'USD'],
        ['name'=>'Brunei','code'=>'BN','currency'=>'BND'],
        ['name'=>'Bulgaria','code'=>'BG','currency'=>'BGN'],
        ['name'=>'Burkina Faso','code'=>'BF','currency'=>'XOF'],
        ['name'=>'Burundi','code'=>'BI','currency'=>'BIF'],
        ['name'=>'Cambodia','code'=>'KH','currency'=>'KHR'],
        ['name'=>'Cameroon','code'=>'CM','currency'=>'XAF'],
        ['name'=>'Canada','code'=>'CA','currency'=>'CAD'],
        ['name'=>'Cape Verde','code'=>'CV','currency'=>'CVE'],
        ['name'=>'Cayman Islands','code'=>'KY','currency'=>'KYD'],
        ['name'=>'Central African Republic','code'=>'CF','currency'=>'XAF'],
        ['name'=>'Chad','code'=>'TD','currency'=>'XAF'],
        ['name'=>'Chile','code'=>'CL','currency'=>'CLP'],
        ['name'=>'China','code'=>'CN','currency'=>'CNY'],
        ['name'=>'Colombia','code'=>'CO','currency'=>'COP'],
        ['name'=>'Comoros','code'=>'KM','currency'=>'KMF'],
        ['name'=>'Congo','code'=>'CG','currency'=>'XAF'],
        ['name'=>'Cook Islands','code'=>'CK','currency'=>'NZD'],
        ['name'=>'Costa Rica','code'=>'CR','currency'=>'CRC'],
        ['name'=>'Croatia','code'=>'HR','currency'=>'HRK'],
        ['name'=>'Cuba','code'=>'CU','currency'=>'CUP'],
        ['name'=>'Curacao','code'=>'CW','currency'=>'ANG'],
        ['name'=>'Cyprus','code'=>'CY','currency'=>'EUR'],
        ['name'=>'Czech Republic','code'=>'CZ','currency'=>'CZK'],
        ['name'=>'Denmark','code'=>'DK','currency'=>'DKK'],
        ['name'=>'Djibouti','code'=>'DJ','currency'=>'DJF'],
        ['name'=>'Dominica','code'=>'DM','currency'=>'XCD'],
        ['name'=>'Dominican Republic','code'=>'DO','currency'=>'DOP'],
        ['name'=>'Ecuador','code'=>'EC','currency'=>'USD'],
        ['name'=>'Egypt','code'=>'EG','currency'=>'EGP'],
        ['name'=>'El Salvador','code'=>'SV','currency'=>'USD'],
        ['name'=>'Equatorial Guinea','code'=>'GQ','currency'=>'XAF'],
        ['name'=>'Eritrea','code'=>'ER','currency'=>'ERN'],
        ['name'=>'Estonia','code'=>'EE','currency'=>'EUR'],
        ['name'=>'Ethiopia','code'=>'ET','currency'=>'ETB'],
        ['name'=>'Fiji','code'=>'FJ','currency'=>'FJD'],
        ['name'=>'Finland','code'=>'FI','currency'=>'EUR'],
        ['name'=>'France','code'=>'FR','currency'=>'EUR'],
        ['name'=>'French Guiana','code'=>'GF','currency'=>'EUR'],
        ['name'=>'French Polynesia','code'=>'PF','currency'=>'XPF'],
        ['name'=>'Gabon','code'=>'GA','currency'=>'XAF'],
        ['name'=>'Gambia','code'=>'GM','currency'=>'GMD'],
        ['name'=>'Georgia','code'=>'GE','currency'=>'GEL'],
        ['name'=>'Germany','code'=>'DE','currency'=>'EUR'],
        ['name'=>'Ghana','code'=>'GH','currency'=>'GHS'],
        ['name'=>'Gibraltar','code'=>'GI','currency'=>'GBP'],
        ['name'=>'Greece','code'=>'GR','currency'=>'EUR'],
        ['name'=>'Greenland','code'=>'GL','currency'=>'DKK'],
        ['name'=>'Grenada','code'=>'GD','currency'=>'XCD'],
        ['name'=>'Guadeloupe','code'=>'GP','currency'=>'EUR'],
        ['name'=>'Guam','code'=>'GU','currency'=>'USD'],
        ['name'=>'Guatemala','code'=>'GT','currency'=>'GTQ'],
        ['name'=>'Guernsey','code'=>'GG','currency'=>'GBP'],
        ['name'=>'Guinea','code'=>'GN','currency'=>'GNF'],
        ['name'=>'Guinea-Bissau','code'=>'GW','currency'=>'XOF'],
        ['name'=>'Guyana','code'=>'GY','currency'=>'GYD'],
        ['name'=>'Haiti','code'=>'HT','currency'=>'USD'],
        ['name'=>'Honduras','code'=>'HN','currency'=>'HNL'],
        ['name'=>'Hong Kong','code'=>'HK','currency'=>'HKD'],
        ['name'=>'Hungary','code'=>'HU','currency'=>'HUF'],
        ['name'=>'Iceland','code'=>'IS','currency'=>'ISK'],
        ['name'=>'India','code'=>'IN','currency'=>'INR'],
        ['name'=>'Indonesia','code'=>'ID','currency'=>'IDR'],
        ['name'=>'Iran','code'=>'IR','currency'=>'IRR'],
        ['name'=>'Iraq','code'=>'IQ','currency'=>'IQD'],
        ['name'=>'Ireland','code'=>'IE','currency'=>'EUR'],
        ['name'=>'Isle of Man','code'=>'IM','currency'=>'GBP'],
        ['name'=>'Israel','code'=>'IL','currency'=>'ILS'],
        ['name'=>'Italy','code'=>'IT','currency'=>'EUR'],
        ['name'=>'Jamaica','code'=>'JM','currency'=>'JMD'],
        ['name'=>'Japan','code'=>'JP','currency'=>'JPY'],
        ['name'=>'Jersey','code'=>'JE','currency'=>'GBP'],
        ['name'=>'Jordan','code'=>'JO','currency'=>'JOD'],
        ['name'=>'Kazakhstan','code'=>'KZ','currency'=>'KZT'],
        ['name'=>'Kenya','code'=>'KE','currency'=>'KES'],
        ['name'=>'Kiribati','code'=>'KI','currency'=>'AUD'],
        ['name'=>'North Korea','code'=>'KP','currency'=>'KPW'],
        ['name'=>'South Korea','code'=>'KR','currency'=>'KRW'],
        ['name'=>'Kyrgyzstan','code'=>'KG','currency'=>'KGS'],
        ['name'=>'Laos','code'=>'LA','currency'=>'LAK'],
        ['name'=>'Latvia','code'=>'LV','currency'=>'EUR'],
        ['name'=>'Lebanon','code'=>'LB','currency'=>'LBP'],
        ['name'=>'Lesotho','code'=>'LS','currency'=>'ZAR'],
        ['name'=>'Liberia','code'=>'LR','currency'=>'LRD'],
        ['name'=>'Libya','code'=>'LY','currency'=>'LYD'],
        ['name'=>'Liechtenstein','code'=>'LI','currency'=>'CHF'],
        ['name'=>'Lithuania','code'=>'LT','currency'=>'EUR'],
        ['name'=>'Luxembourg','code'=>'LU','currency'=>'EUR'],
        ['name'=>'Macao','code'=>'MO','currency'=>'MOP'],
        ['name'=>'Macedonia','code'=>'MK','currency'=>'MKD'],
        ['name'=>'Madagascar','code'=>'MG','currency'=>'MGA'],
        ['name'=>'Malawi','code'=>'MW','currency'=>'MWK'],
        ['name'=>'Malaysia','code'=>'MY','currency'=>'MYR'],
        ['name'=>'Maldives','code'=>'MV','currency'=>'MVR'],
        ['name'=>'Mali','code'=>'ML','currency'=>'XOF'],
        ['name'=>'Malta','code'=>'MT','currency'=>'EUR'],
        ['name'=>'Marshall Islands','code'=>'MH','currency'=>'USD'],
        ['name'=>'Martinique','code'=>'MQ','currency'=>'EUR'],
        ['name'=>'Mauritania','code'=>'MR','currency'=>'MRO'],
        ['name'=>'Mauritius','code'=>'MU','currency'=>'MUR'],
        ['name'=>'Mayotte','code'=>'YT','currency'=>'EUR'],
        ['name'=>'Mexico','code'=>'MX','currency'=>'MXN'],
        ['name'=>'Micronesia','code'=>'FM','currency'=>'USD'],
        ['name'=>'Moldova','code'=>'MD','currency'=>'MDL'],
        ['name'=>'Monaco','code'=>'MC','currency'=>'EUR'],
        ['name'=>'Mongolia','code'=>'MN','currency'=>'MNT'],
        ['name'=>'Montenegro','code'=>'ME','currency'=>'EUR'],
        ['name'=>'Montserrat','code'=>'MS','currency'=>'XCD'],
        ['name'=>'Morocco','code'=>'MA','currency'=>'MAD'],
        ['name'=>'Mozambique','code'=>'MZ','currency'=>'MZN'],
        ['name'=>'Myanmar','code'=>'MM','currency'=>'MMK'],
        ['name'=>'Namibia','code'=>'NA','currency'=>'ZAR'],
        ['name'=>'Nauru','code'=>'NR','currency'=>'AUD'],
        ['name'=>'Nepal','code'=>'NP','currency'=>'NPR'],
        ['name'=>'Netherlands','code'=>'NL','currency'=>'EUR'],
        ['name'=>'New Caledonia','code'=>'NC','currency'=>'XPF'],
        ['name'=>'New Zealand','code'=>'NZ','currency'=>'NZD'],
        ['name'=>'Nicaragua','code'=>'NI','currency'=>'NIO'],
        ['name'=>'Niger','code'=>'NE','currency'=>'XOF'],
        ['name'=>'Nigeria','code'=>'NG','currency'=>'NGN'],
        ['name'=>'Niue','code'=>'NU','currency'=>'NZD'],
        ['name'=>'Norfolk Island','code'=>'NF','currency'=>'AUD'],
        ['name'=>'Northern Mariana Islands','code'=>'MP','currency'=>'USD'],
        ['name'=>'Norway','code'=>'NO','currency'=>'NOK'],
        ['name'=>'Pakistan','code'=>'PK','currency'=>'PKR'],
        ['name'=>'Palau','code'=>'PW','currency'=>'USD'],
        ['name'=>'Panama','code'=>'PA','currency'=>'USD'],
        ['name'=>'Papua New Guinea','code'=>'PG','currency'=>'PGK'],
        ['name'=>'Paraguay','code'=>'PY','currency'=>'PYG'],
        ['name'=>'Peru','code'=>'PE','currency'=>'PEN'],
        ['name'=>'Philippines','code'=>'PH','currency'=>'PHP'],
        ['name'=>'Pitcairn','code'=>'PN','currency'=>'NZD'],
        ['name'=>'Poland','code'=>'PL','currency'=>'PLN'],
        ['name'=>'Portugal','code'=>'PT','currency'=>'EUR'],
        ['name'=>'Puerto Rico','code'=>'PR','currency'=>'USD'],
        ['name'=>'Romania','code'=>'RO','currency'=>'RON'],
        ['name'=>'Russia','code'=>'RU','currency'=>'RUB'],
        ['name'=>'Rwanda','code'=>'RW','currency'=>'RWF'],
        ['name'=>'Reunion','code'=>'RE','currency'=>'EUR'],
        ['name'=>'Saint Barthelemy','code'=>'BL','currency'=>'EUR'],
        ['name'=>'Saint Helena','code'=>'SH','currency'=>'SHP'],
        ['name'=>'Saint Kitts and Nevis','code'=>'KN','currency'=>'XCD'],
        ['name'=>'Saint Lucia','code'=>'LC','currency'=>'XCD'],
        ['name'=>'Saint Martin','code'=>'MF','currency'=>'EUR'],
        ['name'=>'Saint Pierre','code'=>'PM','currency'=>'EUR'],
        ['name'=>'Saint Vincent','code'=>'VC','currency'=>'XCD'],
        ['name'=>'Samoa','code'=>'WS','currency'=>'WST'],
        ['name'=>'San Marino','code'=>'SM','currency'=>'EUR'],
        ['name'=>'Sao Tome','code'=>'ST','currency'=>'STD'],
        ['name'=>'Senegal','code'=>'SN','currency'=>'XOF'],
        ['name'=>'Serbia','code'=>'RS','currency'=>'RSD'],
        ['name'=>'Seychelles','code'=>'SC','currency'=>'SCR'],
        ['name'=>'Sierra Leone','code'=>'SL','currency'=>'SLL'],
        ['name'=>'Singapore','code'=>'SG','currency'=>'SGD'],
        ['name'=>'Sint Maarten','code'=>'SX','currency'=>'ANG'],
        ['name'=>'Slovakia','code'=>'SK','currency'=>'EUR'],
        ['name'=>'Slovenia','code'=>'SI','currency'=>'EUR'],
        ['name'=>'Solomon Islands','code'=>'SB','currency'=>'SBD'],
        ['name'=>'Somalia','code'=>'SO','currency'=>'SOS'],
        ['name'=>'South Africa','code'=>'ZA','currency'=>'ZAR'],
        ['name'=>'South Sudan','code'=>'SS','currency'=>'SSP'],
        ['name'=>'Spain','code'=>'ES','currency'=>'EUR'],
        ['name'=>'Sri Lanka','code'=>'LK','currency'=>'LKR'],
        ['name'=>'Sudan','code'=>'SD','currency'=>'SDG'],
        ['name'=>'Suriname','code'=>'SR','currency'=>'SRD'],
        ['name'=>'Sweden','code'=>'SE','currency'=>'SEK'],
        ['name'=>'Switzerland','code'=>'CH','currency'=>'CHF'],
        ['name'=>'Syria','code'=>'SY','currency'=>'SYP'],
        ['name'=>'Taiwan','code'=>'TW','currency'=>'TWD'],
        ['name'=>'Tajikistan','code'=>'TJ','currency'=>'TJS'],
        ['name'=>'Tanzania','code'=>'TZ','currency'=>'TZS'],
        ['name'=>'Thailand','code'=>'TH','currency'=>'THB'],
        ['name'=>'Timor-Leste','code'=>'TL','currency'=>'USD'],
        ['name'=>'Togo','code'=>'TG','currency'=>'XOF'],
        ['name'=>'Tokelau','code'=>'TK','currency'=>'NZD'],
        ['name'=>'Tonga','code'=>'TO','currency'=>'TOP'],
        ['name'=>'Trinidad and Tobago','code'=>'TT','currency'=>'TTD'],
        ['name'=>'Tunisia','code'=>'TN','currency'=>'TND'],
        ['name'=>'Turkey','code'=>'TR','currency'=>'TRY'],
        ['name'=>'Turkmenistan','code'=>'TM','currency'=>'TMT'],
        ['name'=>'Turks and Caicos','code'=>'TC','currency'=>'USD'],
        ['name'=>'Tuvalu','code'=>'TV','currency'=>'AUD'],
        ['name'=>'Uganda','code'=>'UG','currency'=>'UGX'],
        ['name'=>'Ukraine','code'=>'UA','currency'=>'UAH'],
        ['name'=>'Uruguay','code'=>'UY','currency'=>'UYU'],
        ['name'=>'Uzbekistan','code'=>'UZ','currency'=>'UZS'],
        ['name'=>'Vanuatu','code'=>'VU','currency'=>'VUV'],
        ['name'=>'Venezuela','code'=>'VE','currency'=>'VEF'],
        ['name'=>'Vietnam','code'=>'VN','currency'=>'VND'],
        ['name'=>'Virgin Islands (British)','code'=>'VG','currency'=>'USD'],
        ['name'=>'Virgin Islands (US)','code'=>'VI','currency'=>'USD'],
        ['name'=>'Wallis and Futuna','code'=>'WF','currency'=>'XPF'],
        ['name'=>'Western Sahara','code'=>'EH','currency'=>'MAD'],
        ['name'=>'Yemen','code'=>'YE','currency'=>'YER'],
        ['name'=>'Zambia','code'=>'ZM','currency'=>'ZMW'],
        ['name'=>'Zimbabwe','code'=>'ZW','currency'=>'ZWL'],
        ['name'=>'Aland Islands','code'=>'AX','currency'=>'EUR'],
        ];

        $sorting = 1;

        foreach ($countries as $c) {

            // ❌ skip existing countries
            if (in_array($c['code'], ['KW','SA','QA','OM','BH','AE','US','GB'])) {
                continue;
            }

            $rate = $rates[$c['currency']] ?? null;

            if (!$rate) {
                $this->warn("Rate missing for {$c['currency']}");
                continue;
            }

            // ✅ Insert admin_country
            $countryId = DB::table('admin_country')->insertGetId([
                'can_edit' => 1,
                'sorting' => $sorting++,
                'shipping_charge' => '20',
                'is_default' => '0',
                'is_active' => 0,
                'country_code' => $c['code'],
                'currency_code' => $c['currency'],
                'currency_rate' => round($rate, 2),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // ✅ Insert description
            DB::table('admin_country_description')->insert([
                'country_id' => $countryId,
                'language_id' => 1,
                'country_name' => $c['name'],
                'delivery_time' => '7-12',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->info("Inserted: {$c['name']} ({$rate})");
        }

        $this->info("✅ DONE - All countries imported with correct rates");
    }
}