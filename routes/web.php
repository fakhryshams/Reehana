<?php

use Illuminate\Support\Facades\Route;
// use Spatie\Honeypot\ProtectAgainstSpam;


// Route::group(['domain' => 'manage.reehana.com'], function () #Domain name (For Admin) here
// {
Route::get('/', function () {
	return redirect(route('admin.login')); //Temporary Redirect
})->name('admin.index');

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['guest:admin']], function () {
	Route::get('/login', 'Auth\AdminLoginController@showLoginForm')->name('login');
	Route::get('/forgot-password', 'Auth\AdminLoginController@forgotPassword')->name('forgot-password');
	Route::get('/reset-password', 'Auth\AdminLoginController@resetPasswordPage')->name('reset-password');
	Route::post('/reset-password', 'Auth\AdminLoginController@resetPassword')->name('reset-password');
	Route::post('/send-reset-link', 'Auth\AdminLoginController@sendResetLink')->name('send-reset-link');
	Route::post('/login', 'Auth\AdminLoginController@login')->name('login');
});
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth:admin']], function () {
	Route::post('/logout', 'Auth\AdminLoginController@logout')->name('logout');
});

Route::group(['namespace' => 'Admin', 'middleware' => ['can:' . config('custom_middleware.view_admin'), 'auth:admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
	//Home-Dashboard
	Route::group(['namespace' => 'Home', 'prefix' => 'home', 'as' => 'home.', 'middleware' => ['can:' . config('custom_middleware.view_dashboard')]], function () {
		$default = "index";
		$controller = "HomeController@";
		Route::get('/', $controller . $default)->name('index'); //Default Page
	});
	//Profile
	Route::group(['namespace' => 'Home', 'prefix' => 'home', 'as' => 'home.', 'middleware' => ['can:' . config('custom_middleware.view_profile')]], function () {
		$default = "index";
		$controller = "HomeController@";
		Route::get('/profile', ['middleware' => ['can:' . config('custom_middleware.view_profile')], 'uses' => $controller . 'profile'])->name('profile'); //Profile
		Route::get('/edit-profile', ['middleware' => ['can:' . config('custom_middleware.edit_profile')], 'uses' => $controller . 'editProfile'])->name('edit-profile'); //Edit Profile
		Route::post('/updtate-profile', ['middleware' => ['can:' . config('custom_middleware.edit_profile')], 'uses' => $controller . 'updateProfile'])->name('update-profile'); //Update Profile
		Route::get('/change-password', ['middleware' => ['can:' . config('custom_middleware.edit_profile')], 'uses' => $controller . 'changePassword'])->name('change-password'); // Edit
		Route::post('/update-password', ['middleware' => ['can:' . config('custom_middleware.edit_profile')], 'uses' => $controller . 'updatePassword'])->name('update-password'); // Update
	});

	Route::group(['namespace' => 'Order', 'prefix' => 'order', 'as' => 'order.', 'middleware' => ['can:' . config('custom_middleware.view_order')]], function () {
		$default = "index";
		$controller = "OrderController@";

		Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_order_order')], 'uses' => $controller . $default])->name('index'); //Default Page
		Route::get('/view-order/{id}', ['middleware' => ['can:' . config('custom_middleware.view_order_order')], 'uses' => $controller . 'vieworder'])->name('vieworder'); //Add Form
		Route::post('/update-order', ['middleware' => ['can:' . config('custom_middleware.edit_order')], 'uses' => $controller . 'updateOrderStatus'])->name('update-order');
		Route::post('/create-shipment', ['middleware' => ['can:' . config('custom_middleware.edit_order')], 'uses' => $controller . 'createShipment'])->name('create-shipment');
		Route::post('/get-tracking-history', ['middleware' => ['can:' . config('custom_middleware.view_order_order')], 'uses' => $controller . 'getTrackingHistory'])->name('get-tracking-history');
		Route::post('/save-note', ['middleware' => ['can:' . config('custom_middleware.edit_order')], 'uses' => $controller . 'saveNote'])->name('save-note');
		Route::post('/update-address', ['middleware' => ['can:' . config('custom_middleware.edit_order')], 'uses' => $controller . 'updateAddress'])->name('update-address');
		Route::get('/delivery-print-order/{id}', ['middleware' => ['can:' . config('custom_middleware.view_order_order')], 'uses' => $controller . 'deliveryprintOrder'])->name('delivery-print-order');

		Route::get('/cancel-order/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order')], 'uses' => $controller . 'cancelOrderStatus'])->name('cancel-order');
		Route::get('/print-invoice/{id}', ['middleware' => ['can:' . config('custom_middleware.view_order_order')], 'uses' => $controller . 'printOrder'])->name('print-order');
		Route::get('/order-index-export', ['middleware' => ['can:' . config('custom_middleware.view_order_order')], 'uses' => $controller . 'orderIndexExport'])->name('order-index-export');
		Route::get('/incomplete-export', ['middleware' => ['can:' . config('custom_middleware.view_order_order')], 'uses' => $controller . 'orderNotProcessExport'])->name('order-not-complete-export');
		Route::get('/incomplete', ['middleware' => ['can:' . config('custom_middleware.view_order_order')], 'uses' => $controller . 'orderNotCompleteData'])->name('order-not-complete');
		Route::get('/incomplete/view-order/{id}', ['middleware' => ['can:' . config('custom_middleware.view_order_order')], 'uses' => $controller . 'vieworder'])->name('order-not-complete.vieworder'); //Add Form
		
		/* Customized Order Routes */
		Route::get('/customized-export', ['middleware' => ['can:' . config('custom_middleware.view_order_customized_order_list')], 'uses' => $controller . 'customizedOrderExport'])->name('customized-order-export');
		Route::get('/customized-incomplete-export', ['middleware' => ['can:' . config('custom_middleware.view_order_customized_order_list')], 'uses' => $controller . 'customizedIncompleteOrderExport'])->name('customized-incomplete-order-export');
		Route::get('/customized', ['middleware' => ['can:' . config('custom_middleware.view_order_customized_order_list')], 'uses' => $controller . 'customizedOrderData'])->name('customized-order');
		Route::get('/customized-pending', ['middleware' => ['can:' . config('custom_middleware.view_order_customized_order_list')], 'uses' => $controller . 'customizedPendingOrderData'])->name('customized-pending-order');
		Route::get('/customized/view-order/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_order_customized_order_list')], 'uses' => $controller . 'customizedViewOrder'])->name('customized-order.vieworder');
		Route::get('/customized-pending/view-order/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_order_customized_order_list')], 'uses' => $controller . 'customizedViewOrder'])->name('customized-pending-order.vieworder');
		Route::post('/customized/update-order/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_customized_order_list')], 'uses' => $controller . 'updateDueAmount'])->name('customized-order.update-order');
		Route::get('/convert-to-order/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_customized_order_list')], 'uses' => $controller . 'convertQuotationToOrder'])->name('convert-to-order');
		Route::get('/process-order/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_order')], 'uses' => $controller . 'processedOrder'])->name('process-order');
		Route::get('/delete/{id}/{page}', ['middleware' => ['can:' . config('custom_middleware.delete_order_order')], 'uses' => $controller . 'deleteOrder'])->name('delete'); 
		Route::get('/request-pickup/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_order')], 'uses' => $controller . 'requestPickup'])->name('request-pickup');
		Route::post('/upload-documents/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_order')], 'uses' => $controller . 'uploadDocuments'])->name('upload-documents');
		Route::get('/recover-order/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_order')], 'uses' => $controller . 'recoverOrder'])->name('recover-order'); 
		Route::get('/customized-quotation-to-po', ['middleware' => ['can:' . config('custom_middleware.view_order_customized_order_list')], 'uses' => $controller . 'customizedOrderQuotationData'])->name('customized-quotation-to-po');
		Route::get('/customized-quotation-to-po/view-order/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_order_customized_order_list')], 'uses' => $controller . 'customizedViewOrder'])->name('customized-quotation-to-po.vieworder');
		Route::get('/customized-quotation-to-po-export', ['middleware' => ['can:' . config('custom_middleware.view_order_customized_order_list')], 'uses' => $controller . 'customizedOrderQuotationExport'])->name('customized-quotation-to-po-export');

		/* Temporary Orders Routes */
		Route::get('/temporary-orders', ['middleware' => ['can:' . config('custom_middleware.view_order_customized_order_list')], 'uses' => $controller . 'temporaryOrderData'])->name('temporary-orders');
		Route::get('/temporary-orders/view-order/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_order_customized_order_list')], 'uses' => $controller . 'viewTemporaryOrderRoute'])->name('temporary-orders.vieworder');
		Route::get('/temporary-orders/view/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_order_customized_order_list')], 'uses' => $controller . 'temporaryOrderDetail'])->name('temporary-orders.view');
		Route::post('/temporary-orders/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_customized_order_list')], 'uses' => $controller . 'updateTemporaryOrder'])->name('temporary-orders.update');
		Route::get('/temporary-orders/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_order_order')], 'uses' => $controller . 'deleteTemporaryOrder'])->name('temporary-orders.delete');
		//Delete Order

		Route::post('/customized/update-discount/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_customized_order_list')], 'uses' => $controller . 'updateDiscount'])->name('customized-order.update-discount');
		Route::post('/customized/update-shipping/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_customized_order_list')], 'uses' => $controller . 'updateShipping'])->name('customized-order.update-shipping');
		Route::post('/customized/update-item-detail', ['middleware' => ['can:' . config('custom_middleware.edit_order_customized_order_list')], 'uses' => $controller . 'updateItemDetail'])->name('customized-order.update-item-detail');
		Route::post('/customized/update-full-order', ['middleware' => ['can:' . config('custom_middleware.edit_order_customized_order_list')], 'uses' => $controller . 'updateFullOrder'])->name('customized-order.update-full-order');
		Route::post('/customized/generate-payment-url/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_customized_order_list')], 'uses' => $controller . 'generatePaymentLink'])->name('customized-order.generate-payment-url');
		Route::post('/customized/send-payment-email/{paymentLinkId}', ['middleware' => ['can:' . config('custom_middleware.edit_order_customized_order_list')], 'uses' => $controller . 'sendPaymentLinkEmail'])->name('customized-order.send-payment-email');
		Route::post('/customized/record-manual-payment/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_order_customized_order_list')], 'uses' => $controller . 'recordManualPayment'])->name('customized-order.record-manual-payment');

		/*
				             * wishlist view
			*/
		Route::group(['prefix' => 'wishlist', 'as' => 'wishlist.', 'middleware' => ['can:' . config('custom_middleware.view_order_wish_list')]], function () {
			$default = "index";
			$controller = "OrderWishlistController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_order_wish_list')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/view/{id}', ['middleware' => ['can:' . config('custom_middleware.view_order_wish_list')], 'uses' => $controller . 'view'])->name('view'); //view data
			Route::get('/send-email/{id}/{w_id?}', ['middleware' => ['can:' . config('custom_middleware.view_order_wish_list')], 'uses' => $controller . 'sendEmail'])->name('mail'); //send email
			Route::get('/export-wishlist', ['middleware' => ['can:' . config('custom_middleware.view_order_wish_list')], 'uses' => $controller . 'exportWishlistData'])->name('export-wishlist'); //export excel
		});
	});
	/*
			         * Product
		*/
	Route::group(['namespace' => 'Product', 'prefix' => 'product', 'as' => 'product.', 'middleware' => ['can:' . config('custom_middleware.view_product')]], function () {
		Route::group(['prefix' => '', 'as' => '', 'middleware' => []], function () {
			$default = "index";
			$controller = "ProductController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_product_product')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/out-of-stock', ['middleware' => ['can:' . config('custom_middleware.view_product_product')], 'uses' => $controller . 'outOfStock'])->name('out-of-stock'); //Default Page

			Route::get('/change-sorting/{currentOrder?}/{newOrder?}/{productId?}', ['middleware' => ['can:' . config('custom_middleware.create_product_product')], 'uses' => $controller . 'changeSorting'])->name('change-sorting');

			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_product_product')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_product_product')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::post('/bind-html', ['middleware' => ['can:' . config('custom_middleware.create_product_product')], 'uses' => $controller . 'bindImageHtml'])->name('bind-html'); //Save
			Route::post('/bind-size-quantity', ['middleware' => ['can:' . config('custom_middleware.create_product_product')], 'uses' => $controller . 'bindSizeQuantity'])->name('bind-size-quantity'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_product')], 'uses' => $controller . 'edit'])->name('edit'); //Update Form
			Route::get('/stock/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_product')], 'uses' => $controller . 'stock'])->name('stock'); //stock
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_product')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_product_product')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_product_product')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/change-new-arrival/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_product_product')], 'uses' => $controller . 'changeNewArrival'])->name('change-new-arrival'); //Change New Arrival
			Route::get('/remove-gallery-image/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_product')], 'uses' => $controller . 'removeGalleryImage'])->name('remove-gallery-image'); //Change Status

			Route::get('/remove-attr-image/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_product')], 'uses' => $controller . 'removeAttrImage'])->name('remove-attr-image'); //Change Status

			Route::get('/product-export', ['middleware' => ['can:' . config('custom_middleware.view_product')], 'uses' => $controller . 'productDataExport'])->name('product-export'); //Product Export
			Route::post('/get-subcategory', ['middleware' => ['can:' . config('custom_middleware.create_product_product')], 'uses' => $controller . 'getSubCategory'])->name('get-subcategory'); //get sub category
			Route::post('/get-color-size-html', ['middleware' => ['can:' . config('custom_middleware.create_product_product')], 'uses' => $controller . 'getSizeColorHTML'])->name('getSizeColorHTML'); //getSizeColorHTML
			Route::post('/save-color-size', ['middleware' => ['can:' . config('custom_middleware.create_product_product')], 'uses' => $controller . 'saveSizeColor'])->name('save-color-size'); //Save Color-Size
		});

		Route::group(['namespace' => 'Category', 'prefix' => 'category', 'as' => 'category.', 'middleware' => ['can:' . config('custom_middleware.view_product_category')]], function () {
			$default = "index";
			$controller = "CategoryController@";
			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_product_category')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_product_category')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_product_category')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_category')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_category')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_product_category')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}/{activateAll?}', ['middleware' => ['can:' . config('custom_middleware.edit_product_category')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/category-export', ['middleware' => ['can:' . config('custom_middleware.view_product_category')], 'uses' => $controller . 'categoryDataExport'])->name('category-export'); //Change Status
		});

		Route::group(['namespace' => 'Category', 'prefix' => 'sub-category', 'as' => 'subcategory.', 'middleware' => ['can:' . config('custom_middleware.view_product_sub_category')]], function () {
			$default = "index";
			$controller = "SubCategoryController@";
			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_product_sub_category')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_product_sub_category')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_product_sub_category')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_sub_category')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_sub_category')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_product_sub_category')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_product_sub_category')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/subcategory-export', ['middleware' => ['can:' . config('custom_middleware.view_product_sub_category')], 'uses' => $controller . 'subCategoryExport'])->name('subcategory-export'); //Change Status
		});

		Route::group(['namespace' => 'Brand', 'prefix' => 'brand', 'as' => 'brand.', 'middleware' => ['can:' . config('custom_middleware.view_product_sub_category')]], function () {
			$default = "index";
			$controller = "BrandController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_product_sub_category')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_product_sub_category')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_product_sub_category')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_sub_category')], 'uses' => $controller . 'edit'])->name('edit'); //Add Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_product_sub_category')], 'uses' => $controller . 'update'])->name('update'); //Add Form
		});
	});
	/*
			         * /.Product
		*/

	/*
			         * Master
		*/
	Route::group(['namespace' => 'Master', 'prefix' => 'master', 'as' => 'master.', 'middleware' => ['can:' . config('custom_middleware.view_master')]], function () {
		Route::group(['namespace' => 'Color', 'prefix' => 'color', 'as' => 'color.', 'middleware' => ['can:' . config('custom_middleware.view_master_color')]], function () {
			$default = "index";
			$controller = "ColorController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_master_color')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_color')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_color')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_color')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_color')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_unit')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_master_color')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/export-color', ['middleware' => ['can:' . config('custom_middleware.view_master_color')], 'uses' => $controller . 'exportColorData'])->name('export-color'); //export excel
		});

		Route::group(['namespace' => 'Unit', 'prefix' => 'unit', 'as' => 'unit.', 'middleware' => ['can:' . config('custom_middleware.view_master_unit')]], function () {
			$default = "index";
			$controller = "UnitController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_master_unit')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_unit')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_unit')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_unit')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_unit')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_master_unit')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_master_unit')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
		});

		Route::group(['namespace' => 'Country', 'prefix' => 'country', 'as' => 'country.', 'middleware' => ['can:' . config('custom_middleware.view_master_country')]], function () {
			$default = "index";
			$controller = "CountryController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_master_country')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_country')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_country')], 'uses' => $controller . 'store'])->name('add'); //Add Form
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_country')], 'uses' => $controller . 'edit'])->name('edit'); //Add Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_country')], 'uses' => $controller . 'update'])->name('update'); //Add Form
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_master_country')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_master_country')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/export-country', ['middleware' => ['can:' . config('custom_middleware.view_master_country')], 'uses' => $controller . 'exportCountryData'])->name('export-country'); //export excel
			Route::post('/get-rate', ['middleware' => ['can:' . config('custom_middleware.create_master_country')], 'uses' => $controller . 'getCurrencyRate'])->name('currency-rate'); //Covert Currency Rate
		});

		Route::group(['namespace' => 'City', 'prefix' => 'area', 'as' => 'city.', 'middleware' => ['can:' . config('custom_middleware.view_master_city')]], function () {
			$default = "index";
			$controller = "CityController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_master_city')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_city')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_city')], 'uses' => $controller . 'store'])->name('add'); //Add Form
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_city')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_city')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_master_city')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_master_city')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/export-city', ['middleware' => ['can:' . config('custom_middleware.view_master_city')], 'uses' => $controller . 'exportCityData'])->name('export-city'); //export city
		});

		Route::group(['namespace' => 'Zone', 'prefix' => 'zone', 'as' => 'zone.', 'middleware' => ['can:' . config('custom_middleware.view_master_zone')]], function () {
			$default = "index";
			$controller = "ZoneController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_master_zone')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_zone')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_zone')], 'uses' => $controller . 'store'])->name('add'); //Add Form
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_zone')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_zone')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_master_zone')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_master_zone')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/export-zone', ['middleware' => ['can:' . config('custom_middleware.view_master_zone')], 'uses' => $controller . 'exportCityData'])->name('export-zone'); //export zone
		});

		/*
				             * Size
			*/
		Route::group(['namespace' => 'Size', 'prefix' => 'size', 'as' => 'size.', 'middleware' => ['can:' . config('custom_middleware.view_master_size')]], function () {
			$default = "index";
			$controller = "SizeController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_master_size')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_size')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_size')], 'uses' => $controller . 'store'])->name('add'); //Add Form
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_size')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_size')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_master_size')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_master_size')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/export-size', ['middleware' => ['can:' . config('custom_middleware.view_master_size')], 'uses' => $controller . 'exportSizeData'])->name('export-size'); //export size
		});
		/*
				             * Courier Company
			*/
		Route::group(['namespace' => 'CourierCompany', 'prefix' => 'courier-company', 'as' => 'courier-company.', 'middleware' => ['can:' . config('custom_middleware.view_master_courier_company')]], function () {
			$default = "index";
			$controller = "CourierCompanyController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_master_courier_company')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_courier_company')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_courier_company')], 'uses' => $controller . 'store'])->name('add'); //Add Form
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_courier_company')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_courier_company')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_master_courier_company')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_master_courier_company')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
		});
		/*
				             * Coupon
			*/
		Route::group(['namespace' => 'Coupon', 'prefix' => 'coupon', 'as' => 'coupon.', 'middleware' => ['can:' . config('custom_middleware.view_master_coupon')]], function () {
			$default = "index";
			$controller = "CouponController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_master_coupon')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_coupon')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_master_coupon')], 'uses' => $controller . 'store'])->name('add'); //Add Form
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_coupon')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_coupon')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_master_coupon')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_master_coupon')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/send-mail/{id}/', ['middleware' => ['can:' . config('custom_middleware.view_master_coupon')], 'uses' => $controller . 'sendMail'])->name('send-mail'); //Send Mail
			Route::post('/send-coupon/', ['middleware' => ['can:' . config('custom_middleware.view_master_coupon')], 'uses' => $controller . 'sendCoupon'])->name('send-coupon'); //Send Mail of Coupon
		});

		/*
				             * Brand
			*/
		Route::group(['namespace' => 'Brand', 'prefix' => 'brand', 'as' => 'brand.', 'middleware' => ['can:' . config('custom_middleware.view_master')]], function () {
			$default = "index";
			$controller = "BrandController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_master')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.view_master')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.view_master')], 'uses' => $controller . 'store'])->name('add'); //Add Form
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.view_master')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.view_master')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.view_master')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.view_master')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/export-brand', ['middleware' => ['can:' . config('custom_middleware.view_master')], 'uses' => $controller . 'exportBrandData'])->name('export-brand'); //export brand
		});

		Route::group(['namespace' => 'PhysicalStore', 'prefix' => 'physical-store', 'as' => 'physical-store.', 'middleware' => ['can:' . config('custom_middleware.view_physical_store')]], function () {
		$controller = 'PhysicalStoreController@';
			Route::get('/', ['uses' => $controller . 'index'])->name('index');
			Route::get('/add', ['uses' => $controller . 'create'])->name('add');
			Route::post('/add', ['uses' => $controller . 'store'])->name('add');
			Route::get('/edit/{id}', ['uses' => $controller . 'edit'])->name('edit');
			Route::post('/update/{id}', ['uses' => $controller . 'update'])->name('update');
			Route::get('/delete/{id}', ['uses' => $controller . 'delete'])->name('delete');
			Route::get('/change-status/{id}/{active}', ['uses' => $controller . 'changeStatus'])->name('change-status');
			Route::post('/get-city', ['uses' => $controller . 'GetCity'])->name('get_city');
		});

		Route::group(['namespace' => 'WarehouseAddress', 'prefix' => 'warehouse-address', 'as' => 'warehouse-address.', 'middleware' => ['can:' . config('custom_middleware.view_warehouse_address')]], function () {
			$controller = 'WarehouseAddressController@';
			Route::get('/', ['uses' => $controller . 'index'])->name('index');
			Route::get('/edit/{id}', ['uses' => $controller . 'edit'])->name('edit');
			Route::post('/update/{id}', ['uses' => $controller . 'update'])->name('update');
		});
	});

	/*
			         * /.Master
		*/

	Route::group(['namespace' => 'Pages', 'prefix' => 'page', 'as' => 'page.', 'middleware' => ['can:' . config('custom_middleware.view_pages')]], function () {
		Route::group(['prefix' => '', 'as' => '', 'middleware' => ['can:' . config('custom_middleware.view_home_slider')]], function () {
			$default = "index";
			$controller = "GeneralSettingController@";
			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_general_setting')], 'uses' => $controller . $default])->name('index'); //Default Page
		});

		Route::group(['namespace' => 'HomeSlider', 'prefix' => 'home-slider', 'as' => 'home-slider.', 'middleware' => ['can:' . config('custom_middleware.view_pages_home_slider')]], function () {
			$default = "index";
			$controller = "HomeSliderController@";
			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_pages_home_slider')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_home_slider')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_home_slider')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_general_setting_setting')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
		});

		Route::group(['namespace' => 'OurCollection', 'prefix' => 'our-collection', 'as' => 'our-collection.', 'middleware' => ['can:' . config('custom_middleware.view_pages_our_collection')]], function () {
			$default = "index";
			$controller = "OurCollectionController@";
			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_pages_our_collection')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_our_collection')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_our_collection')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_general_setting_setting')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
		});

		/*
				             * About Us Page
			*/
		Route::group(['namespace' => 'AboutUs', 'prefix' => 'about-us', 'as' => 'about-us.', 'middleware' => ['can:' . config('custom_middleware.view_pages_about_us')]], function () {
			$default = "index";
			$controller = "AboutUsController@";

			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_about_us')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_about_us')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::post('/update', ['middleware' => ['can:' . config('custom_middleware.edit_pages_about_us')], 'uses' => $controller . 'update'])->name('update'); //Update
		});
		/*
				             * Shipping Policy
			*/
		Route::group(['namespace' => 'ShippingPolicy'], function () {
			Route::group(['prefix' => 'shipping-policy', 'as' => 'shipping-policy.', 'middleware' => ['can:' . config('custom_middleware.view_pages_shipping_policy')]], function () {
				$default = "index";
				$controller = "ShippingPolicyController@";
				Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_shipping_policy')], 'uses' => $controller . 'create'])->name('add'); //Add Form
				Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_shipping_policy')], 'uses' => $controller . 'store'])->name('add'); //Save
				Route::post('/update', ['middleware' => ['can:' . config('custom_middleware.edit_pages_shipping_policy')], 'uses' => $controller . 'update'])->name('update'); //Update
			});

			Route::group(['prefix' => 'privacy-policy', 'as' => 'privacy-policy.', 'middleware' => ['can:' . config('custom_middleware.view_pages_privacy_policy')]], function () {
				$default = "index";
				$controller = "ShippingPolicyController@";
				Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_shipping_policy')], 'uses' => $controller . 'createPrivacyPolicy'])->name('add'); //Add Form
				Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_shipping_policy')], 'uses' => $controller . 'storePrivacyPolicy'])->name('add'); //Save
				Route::post('/update', ['middleware' => ['can:' . config('custom_middleware.edit_pages_shipping_policy')], 'uses' => $controller . 'updatePrivacyPolicy'])->name('update'); //Update
			});

			Route::group(['prefix' => 'terms', 'as' => 'terms.', 'middleware' => ['can:' . config('custom_middleware.view_pages_t_and_c')]], function () {
				$default = "index";
				$controller = "ShippingPolicyController@";
				Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_shipping_policy')], 'uses' => $controller . 'createTerms'])->name('add'); //Add Form
				Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_shipping_policy')], 'uses' => $controller . 'storeTerms'])->name('add'); //Save
				Route::post('/update', ['middleware' => ['can:' . config('custom_middleware.edit_pages_shipping_policy')], 'uses' => $controller . 'updateTerms'])->name('update'); //Update
			});
		});

		/*
				             * Return Policy
			*/
		Route::group(['namespace' => 'ReturnPolicy', 'prefix' => 'return-policy', 'as' => 'return-policy.', 'middleware' => ['can:' . config('custom_middleware.view_pages_return_policy')]], function () {
			$default = "index";
			$controller = "ReturnPolicyController@";
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_return_policy')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_return_policy')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::post('/update', ['middleware' => ['can:' . config('custom_middleware.edit_pages_return_policy')], 'uses' => $controller . 'update'])->name('update'); //Update
		});

		Route::group(['namespace' => 'ContactUs', 'prefix' => 'contact-us', 'as' => 'contact-us.', 'middleware' => ['can:' . config('custom_middleware.view_pages_contact_us')]], function () {
			$default = "index";
			$controller = "ContactUsController@";
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_contact_us')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_pages_contact_us')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::post('/update', ['middleware' => ['can:' . config('custom_middleware.edit_pages_contact_us')], 'uses' => $controller . 'update'])->name('update'); //Update
		});
	});

	/*
			         * /.Pages
		*/

	/*
			         * General Setting
		*/

	Route::group(['namespace' => 'GeneralSetting', 'prefix' => 'general-setting', 'as' => 'general-setting.', 'middleware' => ['can:' . config('custom_middleware.view_general_setting')]], function () {
		Route::group(['prefix' => '', 'as' => '', 'middleware' => ['can:' . config('custom_middleware.view_home_slider')]], function () {
			$default = "index";
			$controller = "GeneralSettingController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_general_setting')], 'uses' => $controller . $default])->name('index'); //Default Page
			//Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_general_setting')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			//Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting')], 'uses' => $controller . 'store'])->name('add'); //Save
			//Route::post('/bind-html', ['middleware' => ['can:' . config('custom_middleware.delete_general_setting')], 'uses' => $controller . 'bindImageHtml'])->name('bind-html'); //Save
		});

		Route::group(['namespace' => 'Setting', 'prefix' => 'setting', 'as' => 'setting.', 'middleware' => ['can:' . config('custom_middleware.view_general_setting_setting')]], function () {
			$default = "index";
			$controller = "SettingController@";
			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_general_setting_setting')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/edit', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'editSetting'])->name('edit-setting'); //Edit All
			Route::post('/update', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'updateSetting'])->name('update-setting'); //Update All
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Unique Record
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'update'])->name('update'); //Update Unique Record
			Route::get('/discount/{type?}/{id?}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'discount'])->name('discount'); //Update Unique Record
			Route::post('/updateDiscount', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_setting')], 'uses' => $controller . 'updateDiscount'])->name('updateDiscount'); //Update Unique Record
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_master_unit')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_master_color')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status

		});

		/*
				             * Payment Gateway
			*/
		Route::group(['namespace' => 'PaymentGateway', 'prefix' => 'payment-gateway', 'as' => 'payment-gateway.', 'middleware' => ['can:' . config('custom_middleware.view_general_setting_payment_gateway')]], function () {
			$default = "index";
			$controller = "PaymentGatewayController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_general_setting_payment_gateway')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_general_setting_payment_gateway')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_general_setting_payment_gateway')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_payment_gateway')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_payment_gateway')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_general_setting_payment_gateway')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_general_setting_payment_gateway')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
		});
	});

	/*
			         * /.General Setting
		*/

	Route::group(['namespace' => 'Customer', 'prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['can:' . config('custom_middleware.view_customer')]], function () {
		$default = "index";
		$controller = "CustomerController@";
		Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_customer')], 'uses' => $controller . $default])->name('index'); //Default Page
		Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_customer')], 'uses' => $controller . 'create'])->name('add'); //Add Form
		Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_customer')], 'uses' => $controller . 'store'])->name('add'); //Save
		Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_customer')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
		Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_customer')], 'uses' => $controller . 'update'])->name('update'); //Update
		Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_customer')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
		Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_customer')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
		Route::get('/export-customer', ['middleware' => ['can:' . config('custom_middleware.view_customer')], 'uses' => $controller . 'exportCustomerData'])->name('export-customer'); //export customer
		Route::group(['namespace' => 'Address', 'prefix' => 'address', 'as' => 'address.', 'middleware' => ['can:' . config('custom_middleware.view_customer')]], function () {
			$default = "index";
			$controller = "AddressController@";
			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_customer')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_customer')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_customer')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_customer')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_customer')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_customer')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_customer')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::post('/get-city', ['middleware' => ['can:' . config('custom_middleware.create_customer')], 'uses' => $controller . 'getCity'])->name('get-city'); //Ajax - Get City
		});
	});

	/*
			         * Report
		*/
	Route::group(['namespace' => 'Report', 'prefix' => 'report', 'as' => 'report.', 'middleware' => ['can:' . config('custom_middleware.view_report')]], function () {
		$default = "index";
		$controller = "ReportController@";
		Route::get('/product-like', ['middleware' => ['can:' . config('custom_middleware.view_report_product_liked')], 'uses' => $controller . 'productLikedReport'])->name('product-like');
		Route::get('/product-like-export', ['middleware' => ['can:' . config('custom_middleware.view_report_product_liked')], 'uses' => $controller . 'productLikedReportExport'])->name('product-like-export');
		Route::get('/sales-datewise', ['middleware' => ['can:' . config('custom_middleware.view_report_sales_date')], 'uses' => $controller . 'salesReportDateWise'])->name('sales-date');
		Route::get('/sales-datewise-export', ['middleware' => ['can:' . config('custom_middleware.view_report_sales_date')], 'uses' => $controller . 'salesReportDateWiseExport'])->name('sales-date-export');
		Route::get('/sales-monthwise', ['middleware' => ['can:' . config('custom_middleware.view_report_sales_month')], 'uses' => $controller . 'salesReportMonthWise'])->name('sales-month');
		Route::get('/sales-monthwise-export', ['middleware' => ['can:' . config('custom_middleware.view_report_sales_month')], 'uses' => $controller . 'salesReportMonthWiseExport'])->name('sales-month-export');
		Route::get('/sales-yearwise', ['middleware' => ['can:' . config('custom_middleware.view_report_sales_year')], 'uses' => $controller . 'salesReportYearWise'])->name('sales-year');
		Route::get('/sales-yearwise-export', ['middleware' => ['can:' . config('custom_middleware.view_report_sales_year')], 'uses' => $controller . 'salesReportYearWiseExport'])->name('sales-year-export');
		Route::get('/sales-customerwise', ['middleware' => ['can:' . config('custom_middleware.view_report_sales_customer')], 'uses' => $controller . 'salesReportCustomerWise'])->name('sales-customer');
		Route::get('/sales-customerwise-export', ['middleware' => ['can:' . config('custom_middleware.view_report_sales_customer')], 'uses' => $controller . 'salesReportCustomerWiseExport'])->name('sales-customer-export');
		Route::get('/top-selling-products', ['middleware' => ['can:' . config('custom_middleware.view_report_top_selling_product')], 'uses' => $controller . 'topSellingProducts'])->name('top-selling');
		Route::get('/top-selling-products-export', ['middleware' => ['can:' . config('custom_middleware.view_report_top_selling_product')], 'uses' => $controller . 'topSellingProductsExport'])->name('top-selling-export');
		Route::get('/coupon-report', ['middleware' => ['can:' . config('custom_middleware.view_report_coupon')], 'uses' => $controller . 'couponReport'])->name('coupon-report');
		Route::get('/coupon-report-export', ['middleware' => ['can:' . config('custom_middleware.view_report_coupon')], 'uses' => $controller . 'couponReportExport'])->name('coupon-report-export');
	});

	/*
			         * Contact Us
		*/
	Route::group(['namespace' => 'ContactUs', 'prefix' => 'contact-us', 'as' => 'contact-us.', 'middleware' => ['can:' . config('custom_middleware.view_contact_us')]], function () {
		$default = "index";
		$controller = "ContactUsController@";

		Route::get('/', ['middleware' => [], 'uses' => $controller . $default])->name('index'); //Default
		Route::get('/export', $controller . 'exportToExcel')->name('export'); // Export to Excel
		Route::get('/delete/{id}', ['middleware' => [], 'uses' => $controller . 'delete'])->name('delete'); //Delete
	});

	if (false) {
		Route::group(['namespace' => 'Coupon', 'prefix' => 'coupons', 'as' => 'coupons.', 'middleware' => ['can:' . config('custom_middleware.view_coupon')]], function () {
			$default = "index";
			$controller = "CouponController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_coupon')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_coupon')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_coupon')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_coupon')], 'uses' => $controller . 'edit'])->name('edit'); //Add Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_coupon')], 'uses' => $controller . 'update'])->name('update'); //Add Form
		});
		Route::group(['namespace' => 'ShippingMethod', 'prefix' => 'shipping', 'as' => 'shipping.', 'middleware' => ['can:' . config('custom_middleware.view_shipping_method')]], function () {
			$default = "index";
			$controller = "ShippingMethodController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_shipping_method')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_shipping_method')], 'uses' => $controller . 'edit'])->name('edit'); //Add Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_shipping_method')], 'uses' => $controller . 'update'])->name('update'); //Add Form
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_unit')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
		});
	}

	Route::group(['namespace' => 'Monogram', 'prefix' => 'monogram', 'as' => 'monogram.', 'middleware' => ['can:' . config('custom_middleware.view_monogram')]], function () {
		Route::group(['prefix' => 'monogram-style', 'as' => 'font-style.', 'middleware' => ['can:' . config('custom_middleware.view_monogram_font_style')]], function () {
			$default = "index";
			$controller = "MonogramFontStyleController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_monogram_font_style')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_monogram_font_style')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_monogram_font_style')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_font_style')], 'uses' => $controller . 'edit'])->name('edit'); //Add Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_font_style')], 'uses' => $controller . 'update'])->name('update'); //Add Form
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_monogram_font_style')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_font_style')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			Route::post('/get-view', ['middleware' => ['can:' . config('custom_middleware.create_monogram_font_style')], 'uses' => $controller . 'getImageView'])->name('get-view'); //Get Image View
		});
		Route::group(['prefix' => 'color', 'as' => 'color.', 'middleware' => ['can:' . config('custom_middleware.view_monogram_color')]], function () {
			$default = "index";
			$controller = "MonogramColorController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_monogram_color')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_monogram_color')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_monogram_color')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_color')], 'uses' => $controller . 'edit'])->name('edit'); //Add Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_color')], 'uses' => $controller . 'update'])->name('update'); //Add Form
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_monogram_color')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_color')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
		});
		Route::group(['prefix' => 'placement', 'as' => 'placement.', 'middleware' => ['can:' . config('custom_middleware.view_monogram_placement')]], function () {
			$default = "index";
			$controller = "MonogramPlacementController@";

			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_monogram_placement')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_monogram_placement')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_monogram_color')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_placement')], 'uses' => $controller . 'edit'])->name('edit'); //Add Form
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_placement')], 'uses' => $controller . 'update'])->name('update'); //Add Form
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_monogram_placement')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_placement')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
		});
		if (false) {
			Route::group(['prefix' => 'monogram', 'as' => 'monogram.', 'middleware' => ['can:' . config('custom_middleware.view_monogram_master')]], function () {
				$default = "index";
				$controller = "MonogramController@";

				Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_monogram_master')], 'uses' => $controller . $default])->name('index'); //Default Page
				Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_monogram_master')], 'uses' => $controller . 'create'])->name('add'); //Add Form
				Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_monogram_color')], 'uses' => $controller . 'store'])->name('add'); //Save
				Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_master')], 'uses' => $controller . 'edit'])->name('edit'); //Add Form
				Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_master')], 'uses' => $controller . 'update'])->name('update'); //Add Form
				Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_monogram_master')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
				Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_master')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			});
			Route::group(['prefix' => 'monogram-image', 'as' => 'monogram-image.', 'middleware' => ['can:' . config('custom_middleware.view_monogram_image')]], function () {
				$default = "index";
				$controller = "MonogramImageController@";

				Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_monogram_image')], 'uses' => $controller . $default])->name('index'); //Default Page
				Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_monogram_image')], 'uses' => $controller . 'create'])->name('add'); //Add Form
				Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_monogram_image')], 'uses' => $controller . 'store'])->name('add'); //Save
				Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_image')], 'uses' => $controller . 'edit'])->name('edit'); //Add Form
				Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_image')], 'uses' => $controller . 'update'])->name('update'); //Add Form
				Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_monogram_image')], 'uses' => $controller . 'delete'])->name('delete'); //Delete
				Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_monogram_image')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			});
		}
	});

	Route::group(['namespace' => 'UserManagement', 'prefix' => 'user-management', 'as' => 'user-management.', 'middleware' => ['can:' . config('custom_middleware.view_user_management')]], function () {
		$default = "index";
		$controller = "UserController@";
		Route::get('/', ['uses' => $controller . 'index'])->name('index');
		Route::get('/add', ['uses' => $controller . 'create'])->name('add');
		Route::post('/add', ['uses' => $controller . 'store'])->name('add');
		Route::get('/edit/{id}', ['uses' => $controller . 'edit'])->name('edit');
		Route::post('/update/{id}', ['uses' => $controller . 'update'])->name('update');
		Route::get('/delete/{id}', ['uses' => $controller . 'delete'])->name('delete');
		Route::get('/change-status/{id}/{active}', ['uses' => $controller . 'changeStatus'])->name('change-status');
		Route::get('/change-password/{id}', ['uses' => $controller . 'change_password'])->name('change-password');
		Route::post('/update-password/{id}', ['uses' => $controller . 'update_password'])->name('update-password');
	});
});

Route::group(['namespace' => 'SuperAdmin', 'middleware' => ['can:' . config('custom_middleware.view_super_admin'), 'auth:admin'], 'prefix' => 'super-admin', 'as' => 'super-admin.'], function () {
	Route::group(['namespace' => 'Authorization'], function () {
		Route::group(['prefix' => 'role', 'as' => 'role.', 'middleware' => ['can:' . config('custom_middleware.view_super_admin_role')]], function () {
			$default = "index";
			$controller = "RoleController@";

			Route::get('/index', ['middleware' => ['can:' . config('custom_middleware.view_super_admin_role')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/add', ['middleware' => ['can:' . config('custom_middleware.create_super_admin_role')], 'uses' => $controller . 'create'])->name('add'); //Add Form
			Route::post('/add', ['middleware' => ['can:' . config('custom_middleware.create_super_admin_role')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/edit/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_super_admin_role')], 'uses' => $controller . 'edit'])->name('edit'); //Edit Page
			Route::post('/update/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_super_admin_role')], 'uses' => $controller . 'update'])->name('update'); //Update
			Route::get('/delete/{id}', ['middleware' => ['can:' . config('custom_middleware.delete_super_admin_role')], 'uses' => $controller . 'delete'])->name('delete'); //Update
		});
		Route::group(['prefix' => 'permission', 'as' => 'permission.', 'middleware' => ['can:' . config('custom_middleware.view_super_admin_permission')]], function () {
			$default = "index";
			$controller = "PermissionController@";
			Route::get('/{search?}', ['middleware' => ['can:' . config('custom_middleware.view_super_admin_permission')], 'uses' => $controller . $default])->name('index'); //Default Page
			/* Not in Use
	                  Route::post('/add', $controller . 'create')->name('add'); //Add new
	                  Route::get('/edit/{id}', $controller . 'edit')->name('edit'); //Edit Page
	                  Route::post('/update/{id}', $controller . 'update')->name('update'); //Update
	                  Route::post('/delete', $controller . 'delete')->name('delete'); //Update
	                 *
*/
		});
		Route::group(['prefix' => 'role-permission', 'as' => 'role-permission.', 'middleware' => ['can:' . config('custom_middleware.view_super_admin_role_permission')]], function () {
			$default = "index";
			$controller = "RolePermissionController@";
			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_super_admin_role_permission')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::post('/save', ['middleware' => ['can:' . config('custom_middleware.create_super_admin_role_permission')], 'uses' => $controller . 'saveRolePermission'])->name('save'); //Save Role-Permission
			Route::get('/view-role-permission/{id}', ['middleware' => ['can:' . config('custom_middleware.view_super_admin_role_permission')], 'uses' => $controller . 'viewPermission'])->name('view'); //View Permission
			Route::post('/update-role-permission/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_super_admin_role_permission')], 'uses' => $controller . 'updateRolePermission'])->name('update'); //Update Role Permission
		});
		Route::group(['prefix' => 'user-role', 'as' => 'user-role.', 'middleware' => ['can:' . config('custom_middleware.view_super_admin_user_role')]], function () {
			$default = "index";
			$controller = "UserRoleController@";
			Route::get('/', ['middleware' => ['can:' . config('custom_middleware.view_super_admin_user_role')], 'uses' => $controller . $default])->name('index'); //Default Page
			Route::get('/change-user-role/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_super_admin_user_role')], 'uses' => $controller . 'changeRole'])->name('change-role'); //Change User Role
			Route::post('/update', ['middleware' => ['can:' . config('custom_middleware.edit_super_admin_user_role')], 'uses' => $controller . 'updateRole'])->name('update'); //Change User Role
			Route::get('/bulk-assign', ['middleware' => ['can:' . config('custom_middleware.create_super_admin_user_role')], 'uses' => $controller . 'bulkAssign'])->name('bulkAssign'); //bulkAssign
			Route::get('/add-user', ['middleware' => ['can:' . config('custom_middleware.create_super_admin_user_role')], 'uses' => $controller . 'create'])->name('add'); //Create User
			Route::post('/add-user', ['middleware' => ['can:' . config('custom_middleware.create_super_admin_user_role')], 'uses' => $controller . 'store'])->name('add'); //Save
			Route::get('/change-status/{id}/{active}', ['middleware' => ['can:' . config('custom_middleware.edit_super_admin_user_role')], 'uses' => $controller . 'changeStatus'])->name('change-status'); //Change Status
			// User
			Route::post('/save', ['middleware' => ['can:' . config('custom_middleware.create_super_admin_user_role')], 'uses' => $controller . 'saveUserRole'])->name('save'); //Save User-Role
			Route::get('update-role/{id}', ['middleware' => ['can:' . config('custom_middleware.edit_super_admin_user_role')], 'uses' => $controller . 'updateRole'])->name('update-role'); //update Role
		});
	});
});
// });

Route::group(['namespace' => 'Web', 'as' => 'web.'], function () #Domain Name (For Web)here
{
	Route::group(['namespace' => 'Home'], function () {
		$default = "index";
		$controller = "HomeController@";
		Route::get('/', ['uses' => $controller . $default])->name('index'); //Default Page
		Route::get('/change-language/{id}', ['uses' => $controller . 'changeLanguage'])->name('change_language'); //Default Page
		Route::get('/change-currency/{id}', ['uses' => $controller . 'changeCurrency'])->name('change_currency'); //Change Currency
		Route::post('/recent-order', ['uses' => $controller . 'getLatestOrder'])->name('recent-order-home'); //Default Page
		Route::get('/check-expiry', ['uses' => $controller . 'checkExpiredDiscounts'])->name('check-expiry'); //Change Currency

	});



	Route::group(['namespace' => 'Category', 'as' => 'category.'], function () {

		$controller = "CategoryController@";
		Route::get('/category/{slug?}', ['uses' => $controller . 'index'])->name('category');
	});
	Route::group(['namespace' => 'Product', 'as' => 'product.'], function () {
		$default = "index";
		$controller = "ProductController@";
		/*
			         * New Code
		*/
		Route::get('/latest-collection', ['uses' => $controller . 'latestCollection'])->name('latestCollection'); //LatestCollection
		/*
	         * ./New Code
*/

		Route::get('/product/{slug?}/', ['uses' => $controller . $default])->name('index'); //Product-Category Page
		Route::get('/product-detail/{slug}/{barcode?}', ['uses' => $controller . 'productDetail'])->name('product_detail'); // product detail Page
		Route::post('/add-to-cart', ['uses' => $controller . 'addToCart'])->name('add_to_cart'); // Add to Cart AJAX
		Route::post('/check-cart', ['uses' => $controller . 'checkCartProductExist'])->name('check-cart'); // Check Cart AJAX
		Route::post('/get-size', ['uses' => $controller . 'getSize'])->name('get-size'); // Check Cart AJAX
		Route::post('/get-product-imaage', ['uses' => $controller . 'getProductImaage'])->name('get-product-imaage'); // Check Cart AJAX
		Route::post('/getProduct', ['uses' => $controller . 'getProduct'])->name('getProduct'); //product Page
		Route::get('/whats-new', ['uses' => $controller . 'newArrivals'])->name('new_arrivals'); //New Arrival
		Route::get('/shop-by-brand/{brand?}', ['uses' => $controller . 'shopByBrand'])->name('shop_by_brand'); //shopByBrand
		Route::get('/by-category/{tag}', ['uses' => $controller . 'listByTag'])->name('list_by_tag'); //listByTag
		Route::get('/sale', ['uses' => $controller . 'sale'])->name('sale'); //Sale
		Route::post('/update-quantity', ['uses' => $controller . 'updateQuantity'])->name('update_quantity'); //cart remove
		Route::post('/get-monogram', ['uses' => $controller . 'monogram'])->name('get-monogram'); //cart remove
		Route::get('/get-placement/{id?}/{pid?}', ['uses' => $controller . 'placement'])->name('get-placement'); //cart remove
		Route::get('/remove-monogram/{product_id?}/{size_id?}/{color_id?}/{monogramNo?}', ['uses' => $controller . 'removeMonogram'])->name('remove-monogram'); //cart remove
		Route::get('/get-initials/', ['uses' => $controller . 'getInitials'])->name('get_initials'); //cart remove
		Route::post('/update-gift-warp', ['uses' => $controller . 'updateGiftWarp'])->name('update_gift_warp'); //cart remove
		Route::post('/update-gift-box', ['uses' => $controller . 'updateGiftBox'])->name('update_gift_box'); //cart remove
		Route::post('/add-note', ['uses' => $controller . 'addNote'])->name('add_note'); // add note

		//Route::get('/update-placement', ['uses' => $controller . 'updatePlacement'])->name('add_note'); // add note

		Route::get('/apply_same/{cart_id?}', ['uses' => $controller . 'apply_same'])->name('apply_same'); //shopByBrand

		Route::get('/customized', ['uses' => $controller . 'customizePage'])->name('customize_page'); // add note
		Route::post('/customized', ['uses' => $controller . 'customized'])->name('customize'); // add note
		Route::post('/apply-filter', ['uses' => $controller . 'applyFilter'])->name('apply-filter'); // Apply Filter
		Route::post('/search', ['uses' => $controller . 'search'])->name('searchtext'); //Bind Search Result
		Route::post('/check_coupon_code', ['uses' => $controller . 'checkCouponCode'])->name('check_coupon_code'); //check_coupon_code
		Route::get('/update-monogram', ['uses' => $controller . 'updateMonogram'])->name('update-monogram'); // Check Cart AJAX
	});
	Route::group(['namespace' => 'Wishlist', 'as' => 'wishlist.'], function () {
		$default = "index";
		$controller = "WishlistController@";
		Route::get('/wishlist', ['uses' => $controller . $default])->name('index'); // product detail Page
		Route::group(['middleware' => ['auth:web']], function () use ($controller) {
			Route::post('/add-to-wishlist', ['uses' => $controller . 'addToWishlist'])->name('add_to_wishlist'); // add to wishlist
			Route::get('/remove-wishlist/{id}', ['uses' => $controller . 'removeWishlist'])->name('remove_wishlist'); // remove wishlist
			Route::get('/move-to-wishlist/{id}', ['uses' => $controller . 'moveToWishlist'])->name('move_to_wishlist'); //move to wishlist
			Route::post('/move_to_cart', ['uses' => $controller . 'moveToCart'])->name('move_to_cart'); //move to wishlist
			Route::get('/add-product-wishlist/{id}', ['uses' => $controller . 'addProductWishlist'])->name('add_product_wishlist'); //move to wishlist
		});
	});

	Route::group(['namespace' => 'Cart', 'as' => 'cart.'], function () {
		$default = "index";
		$controller = "CartController@";
		Route::get('/cart', ['uses' => $controller . $default])->name('index'); //cart Page
		Route::get('/remove-cart/{id}/{sizeId?}/{colorId?}', ['uses' => $controller . 'removeCart'])->name('remove_cart'); //cart remove
		Route::get('/customized-item', ['uses' => $controller . 'getCustomizedItem'])->name('customized-item'); //cart Page (kept for backward compat, though now modal)
		Route::get('/customized-preview', ['uses' => $controller . 'customizedPreview'])->name('customized_preview'); //customized order preview
		Route::post('/submit-customized-order', ['uses' => $controller . 'submitCustomizedOrder'])->name('submit_customized_order');
		Route::post('/update-customized-order-quantity', ['uses' => $controller . 'updateCustomizedOrderQuantity'])->name('update_customized_order_quantity');
		Route::post('/get-customer', ['uses' => $controller . 'getCustomerDetails'])->name('get_customer');
		Route::post('/get-customer-address', ['uses' => $controller . 'getCustomerAddressDetails'])->name('get_customer_address');
        Route::post('/remove-customized-order', ['uses' => $controller . 'removeCustomizedOrder'])->name('remove_customized_order');
		Route::get('/get-pending-drafts', ['uses' => $controller . 'getPendingTemporaryOrders'])->name('get_pending_drafts');
		Route::get('/get-draft-detail/{id}', ['uses' => $controller . 'getTemporaryOrderDetail'])->name('get_draft_detail');
	});
	Route::group(['namespace' => 'Checkout', 'as' => 'checkout.'], function () {
		$default = "index";
		$controller = "CheckoutController@";
		Route::get('/checkout', ['uses' => 'CheckoutController@index'])->name('index'); //checkout Page
		Route::get('/checkout/{status}/{id?}', ['uses' => 'CheckoutController@manualPaymentTest'])->name('manual_payment_test'); //checkout Page
		Route::get('/payment-success', ['uses' => $controller . 'paymentSuccess'])->name('payment_success'); //checkout Page
		Route::get('/payment-error', ['uses' => $controller . 'paymentError'])->name('payment_error'); //checkout Page
		Route::get('/payment-summary/{id}/{payment_id}', ['uses' => $controller . 'paymentSummary'])->name('payment_summary'); //checkout Page
		Route::get('/payment-review/{token}', ['uses' => $controller . 'paymentReview'])->name('payment_review');
		Route::get('/payment-reject/{token}', ['uses' => $controller . 'paymentReject'])->name('payment_reject');
		Route::group(['middleware' => ['auth:web']], function () use ($controller) {
			Route::get('/cart-review', ['uses' => $controller . 'cartReview'])->name('cart_review'); //checkout Page
			Route::get('/order-summary/{id}', ['uses' => $controller . 'orderSummary'])->name('order_summary'); //checkout Page
			Route::get('/print-order/{id}/{page}', ['uses' => $controller . 'printOrder'])->name('print-order'); //print-order

			Route::get('/test-order/{id}', ['uses' => $controller . 'test_order'])->name('test-order'); //print-order
			Route::post('/initiate-payment', ['uses' => $controller . 'initiatePayment'])->name('initiatePayment'); //checkout Page
			Route::get('/create-order', ['uses' => $controller . 'createOrder'])->name('create_order'); //checkout Page
			Route::get('/manual-payment/{status}/{id?}', ['uses' => $controller . 'manualPaymentTest'])->name('manual_payment_test');
			Route::get('/change-default-address/{id}', ['uses' => $controller . 'ChangeDefaultAddress'])->name('change_default_address'); //checkout Page
		});
	});
	Route::group(['namespace' => 'User', 'as' => 'user.'], function () {
		$default = "index";
		$controller = "LoginController@";
		Route::get('/login', ['uses' => $controller . 'showLoginForm'])->name('login'); //login Page
		Route::post('/login', ['uses' => $controller . 'login'])->name('login'); //login Page
		//google
		Route::get('/login/google', $controller . 'google_login')->name('google_login');
		Route::get('/login/google/callback', $controller . 'google_login_callback');
		//facebook
		Route::get('login/facebook', $controller . 'facebook_login')->name('facebook_login');
		Route::get('login/facebook/callback', $controller . 'facebook_login_callback');
		Route::get('/register', ['uses' => $controller . 'showRgeisterForm'])->name('register'); //register Page
		Route::post('/register', ['uses' => $controller . 'register'])->name('register'); //register
		Route::get('/logout', ['uses' => $controller . 'logout'])->name('logout');
		Route::post('/guest-login', ['uses' => $controller . 'guestLogin'])->name('guest_login');
		// forgot password
		Route::post('/forgot-password', ['uses' => $controller . 'forotPassword'])->name('forgot_password');
		Route::get('/reset-password', ['uses' => $controller . 'resetPasswordPage'])->name('reset_password');
		Route::post('/reset-password', ['uses' => $controller . 'resetPassword'])->name('reset_password');

		Route::group(['middleware' => ['auth:web']], function () {
			$controller = "UserController@";
			$default = "index";
			Route::get('/profile', ['uses' => $controller . 'profile'])->name('profile'); //profile Page
			Route::post('/update-profile', ['uses' => $controller . 'updateProfile'])->name('update_profile'); //update profile
			Route::get('/my-order', ['uses' => $controller . 'myOrder'])->name('my_order'); //my order Page
			Route::get('/order-detail/{id}', ['uses' => $controller . 'orderDetail'])->name('order_detail'); //Order detail
			Route::get('/order-detail-customized/{id}', ['uses' => $controller . 'orderDetailCustomized'])->name('order_detail_customized'); //Customized order detail
			Route::get('/my-address', ['uses' => $controller . 'myAddress'])->name('my_address'); //my address Page
			Route::post('/get-city', ['uses' => $controller . 'GetCity'])->name('get_city'); //my address Page
			Route::post('/get-address-detail', ['uses' => $controller . 'getAddressDetail'])->name('get_address_detail'); //my address Page
			Route::post('/remove-address', ['uses' => $controller . 'removeAddress'])->name('remove_address'); //my address Page
			Route::post('/save-address', ['uses' => $controller . 'saveAddress'])->name('save_address'); //my address Page
			Route::get('/change-password', ['uses' => $controller . 'showChangePasswordForm'])->name('change_password'); //change password Page
			Route::post('/change-password', ['uses' => $controller . 'changePassword'])->name('change_password'); //change password
			Route::get('/billing-address', ['uses' => $controller . 'billingAddress'])->name('billing_address'); //my address Page
		});
	});

	Route::group(['namespace' => 'Contact'], function () {
		$default = "index";
		$controller = "ContactController@";
		Route::get('/contact-us', ['uses' => $controller . $default])->name('contact'); //Contact us page
		Route::get('/shipping', ['uses' => $controller . 'shipping'])->name('shipping'); //Shipping
		Route::get('/privacy-policy', ['uses' => $controller . 'privacyPolicy'])->name('privacy-policy'); //Privacy Policy
		Route::get('/terms-condition', ['uses' => $controller . 'terms'])->name('terms'); //Terms
		Route::get('/returns', ['uses' => $controller . 'returns'])->name('returns'); //Return
		Route::get('/about', ['uses' => $controller . 'about'])->name('about'); //About
		Route::get('/media', ['uses' => $controller . 'media'])->name('media'); //About
		Route::post('/contact', ['uses' => $controller . 'contactUs'])->name('contact_us'); //Contact Us - Save
		Route::get('/refereshcapcha', ['uses' => $controller . 'refereshCapcha'])->name('refereshcapcha'); //RefreshCaptcha
	});

	Route::group(['namespace' => 'Guest', 'as' => 'guest.'], function () {
		$default = "index";
		$controller = "GuestController@";
		Route::get('/guest-address', ['uses' => $controller . $default])->name('guest_address'); //cart Page
		Route::get('/order-review', ['uses' => $controller . 'orderReview'])->name('order_review'); //cart Page
	});
});

/*
 * Table Change Order - Common Function
 */
Route::group(['prefix' => 'common', 'as' => 'sorting.', 'middleware' => ['auth:admin']], function () {
	$controller = "CommonController@";
	Route::post('/change-table-order', ['middleware' => [], 'uses' => $controller . 'changeOrder'])->name('change-order'); //Change Order
});

Route::group(['prefix' => 'cron', 'as' => 'cron.'], function () {
	$controller = "Admin\Master\Country\CountryController@";
	Route::get('/update-currency-cron-by-sm-19021992', ['middleware' => [], 'uses' => $controller . 'updateCurrencyCron'])->name('update-currency-cron'); //Run Cron to Update currency Rate
});

Route::get("/artisan-command", function () {
	Illuminate\Support\Facades\Artisan::call('cache:clear');
	Illuminate\Support\Facades\Artisan::call('view:clear');
	Illuminate\Support\Facades\Artisan::call('route:clear');

	// Illuminate\Support\Facades\Artisan::call('storage:link');
	// Illuminate\Support\Facades\Artisan::call('migrate:refresh');
	// Illuminate\Support\Facades\Artisan::call('db:seed --class=MenuLevelSeeder');
});

Route::get('/test-fedex', function () {
    try {
        $service = new \App\Services\FedExService();
        return response()->json($service->testConnection());
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::fallback(function () {
	$adminDomain = env('APP_URL_ADMIN', '');
	$domain = substr(Request::root(), 7);
	if ($adminDomain == $domain) {
		return response()->view('errors.404', [], 404);
	} else {
		return response()->view('errors.web.404', [], 404);
	}
});
