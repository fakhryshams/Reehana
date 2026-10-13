<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <title>::: {{Helper::getAppName()?Helper::getAppName()." | ":''}} {{trans('admin.wish-list')}} :::</title>


        <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    </head>
    <body style="background: #fff; font-family: 'Roboto Condensed', sans-serif; font-weight: 400; font-size: 14px; color: #000000; line-height: 1.5em">
        <div style="margin: 0 auto; width: 100%; max-width: 600px; box-sizing: border-box; border: 5px solid rgba(225, 225, 225, 0.3)">
            <div style="text-align: center; padding: 0; background: url({{asset('images/mail/bg-pattern.png')}}) center repeat; background-size: 20% auto;">
                <img src="{{Helper::getAppLogo(1)?Helper::getAppLogo(1): ''}}" >
            </div>
            <div style="padding: 20px;">
                <div>
                    <p>Dear John,</p>
                    <h1 style="font-size: 24px; margin: 20px 0; font-weight: normal; line-height: 1.4em;">Remember the awesome item below?</h1>
                    <p>You saved it in your wishlist and guess what? It's on sale!</p>
                   @forelse($wishlist as $record)
                    <?php
                    $product      = $record->getProductData();
                    $defaultImage = $product ? $product->getProductImages() : '';
                    ?>
                    <div style="text-align: center; margin: 30px 0;">
                        <img src="{{asset(Storage::url($defaultImage?$defaultImage->image_thumb_url:''))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png'))}}'" style="max-width: 100%;">
                    </div>
                    @empty
                    @endforelse
                    <p>Hurry in to make sure you snag your most “Coveted MedCloth Merch” – don’t let this be the one that got away! If you arrive at the site, and the item is already sold out, we’re sorry. But atleast you know you have a great taste! :)</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{route('web.product.product_detail',['slug'=> $product->products_slug])}}" style="display: inline-block; text-decoration: none; padding: 20px 25px; background: #fef4f2; color: #f5907c; font-size: 18px; font-weight: bold; border-radius: 3px;">Grab it!</a>
                    </div>
                </div>
                <div style="color: #f5907c;">
                    <p>Best Wishes,<br>Salma Musab Team</p>
                </div>
            </div>
            <div style="padding: 20px; text-align: center; font-size: 12px; background: #f5907c; color: #ffffff;">
                <div>
                    <span style="display: inline-block; margin: 0 5px;"><a href="https://facebook.com" target="_blank"><img src="fb.png"></a></span>
                    <span style="display: inline-block; margin: 0 5px;"><a href="https://twitter.com" target="_blank"><img src="tw.png"></a></span>
                    <span style="display: inline-block; margin: 0 5px;"><a href="https://instagram.com" target="_blank"><img src="in.png"></a></span>
                </div>
                <span style="display: block">All rights reserved. | <a href="#" style="color: #ffffff;">Unsubscribe from list.</a></span>
            </div>
        </div>
    </body>
</html>