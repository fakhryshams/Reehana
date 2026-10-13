
<?php
$cart = [];
$total = 0;
$language = Helper::getLanguage();
$currency = Helper::getDefaultCurrency();
$currencycode = $currency ? $currency->code : config('custom.currency');
$user = Auth::user();
?>
<!--if  user login  get product from db-->
@if($user)
<!--if  user not login  get product from session-->
<?php
$cart = Helper::getShoppingCart($user->id);
?>
@elseif (isset($_COOKIE['cart']))
<?php
$cart = json_decode($_COOKIE['cart'], true);
?>
@endif
<ul>
    <li>
        <div class="prod-img">
            <a href="detail.html">
                <img src="../img/item-01.png">
            </a>
        </div>
        <div class="prod-detail">
            <div class="v-flex">
                <span class="prod-title">Alexandra Flat Sheet</span>
                <span class="prod-price">180.000 KD</span>
                <span class="prod-qty">Qty: <span>1</span></span>
            </div>
        </div>
    </li>

</ul>