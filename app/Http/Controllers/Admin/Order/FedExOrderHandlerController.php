<?php

namespace App\Http\Controllers\Admin\Order;

use App\AdminModel\AdminCurrency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use App\User;
use App\AdminModel\AdminLanguage;
use App\AdminModel\Order\WebOrder;
use App\UserModel\OrderDetail;
use App\AdminModel\Order\AdminOrderStatus;
use App\AdminModel\Order\AdminOrderStatusHistory;
use App\AdminModel\AdminCountry;
use App\Exports\OrderNotCompleteExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrderDetailExport;
use App\AdminModel\CourierCompany\CourierCompany;
use App\AdminModel\Product\AdminProduct;
use App\AdminModel\Product\AdminProductAttribute;
use App\AdminModel\Order\OrderPaymentLog;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use App\UserModel\UserAddress;
use URL;
use App\AdminModel\Order\PaymentLink;

class FedExOrderHandlerController extends Controller
{
    public function requestPickup(Request $request)
    {
        dd($request->all());
    }
}
