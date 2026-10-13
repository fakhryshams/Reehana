<?php

namespace App\Http\Controllers\Admin\Customer\Address;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
/*
 * Models
 */
use App\User;
use App\UserModel\UserAddress;
use App\AdminModel\AdminCountry;
/*
 * Other
 */
use Helper;
use DB;

class AddressController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.customer_address';

    public function index(Request $request)
    {
        if (isset($request->id))
        {
            /*
             * Get SortKey and SortOrder from helper
             */
            $sorting    = Helper::getOrderColumn($request);
            $sortKey    = $sorting['sortKey'];
            $sortVal    = $sorting['sortVal'];
            $searchText = $request->q;
            $pageLength = $request->pageLength;
            $customerID = $request->id ? $request->id : "";
            /*
             * SubCategory-Parent Name
             */
            $addressOf  = "";
            $user       = User::find($customerID);

            if ($user)
            {
                $userAddress = new UserAddress();
                $userAddress = $userAddress->getAllData($searchText, $sortKey, $sortVal, $customerID, $pageLength);
                return view('admin.customer.address.index', compact('user', 'userAddress', 'sortKey', 'sortVal', 'searchText', 'pageLength'));
            }
            else
            {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.error');
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.error');
        }
        return redirect()->route('admin.customer.index')->with($status, $message_text);
    }

    public function create(Request $request)
    {
        $customerID = $request->id ? $request->id : '';
        if ($customerID)
        {
            $user = User::find($customerID);
            if ($user)
            {
                $isDefault = config('custom.customer_address_default');
                $country   = AdminCountry::getCountryData();
                return view("admin.customer.address.add", compact('user', 'isDefault', 'country'));
            }
            else
            {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.error');
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.error');
        }
        return redirect()->route('admin.customer.index')->with($status, $message_text);
    }

    public function store(Request $request)
    {
        if (isset($request->user_id) && $request->user_id)
        {
            /*
             * Validations
             */
            $validationRule    = [];
            $validationMessage = [];

            $validationRule['address_title'] = 'required';
            $validationRule['first_name']    = 'required';
            $validationRule['last_name']     = 'required';
            $validationRule['email']         = 'required';
            $validationRule['phone']         = 'required';
            $validationRule['is_default']    = 'required';
            $validationRule['country_id']    = 'required';
            $validationRule['city_id']       = 'sometimes|required';
            $validationRule['city']          = 'sometimes|required';
            //$validationRule['area']          = 'required';
            $validationRule['street']        = 'required';
            $validationRule['house_number']  = 'required';

            $validationMessage['address_title.required'] = Lang::get($this->langFile . '.error-customer-address-req-address_title');
            $validationMessage['first_name.required']    = Lang::get($this->langFile . '.error-customer-address-req-first_name');
            $validationMessage['last_name.required']     = Lang::get($this->langFile . '.error-customer-address-req-last_name');
            $validationMessage['email.required']         = Lang::get($this->langFile . '.error-customer-address-req-email');
            $validationMessage['phone.required']         = Lang::get($this->langFile . '.error-customer-address-req-phone');
            $validationMessage['is_default.required']    = Lang::get($this->langFile . '.error-customer-address-req-is_default');
            $validationMessage['country_id.required']    = Lang::get($this->langFile . '.error-customer-address-req-country_id');
            $validationMessage['city_id.required']       = Lang::get($this->langFile . '.error-customer-address-req-city_id');
            $validationMessage['city.required']          = Lang::get($this->langFile . '.error-customer-address-req-city');
            $validationMessage['area.required']          = Lang::get($this->langFile . '.error-customer-address-req-area');
            $validationMessage['street.required']        = Lang::get($this->langFile . '.error-customer-address-req-street');
            $validationMessage['house_number.required']  = Lang::get($this->langFile . '.error-customer-address-req-house_number');

            $validatedData = $request->validate($validationRule, $validationMessage);

            DB::beginTransaction();

            try
            {
                $model                 = new UserAddress();
                $model->user_id        = $request->user_id; //Main Category
                $model->address_title  = $request->address_title;
                $model->first_name     = $request->first_name;
                $model->last_name      = $request->last_name;
                $model->email          = $request->email;
                $model->phone          = $request->phone;
                $model->country_id     = $request->country_id;
                $model->city_id        = $request->city_id;
                $model->city           = $request->city;
                //$model->area           = $request->area;
                $model->street         = $request->street;
                $model->house_number   = $request->house_number;
                $model->floor          = $request->floor;
                $model->appartment     = $request->appartment;
                $model->address_detail = $request->address_detail;
                $model->is_default     = $request->is_default;

                if ($model->save())
                {
                    $changeDefaultFlag = true;
                    if ($request->is_default == 1)
                    {
                        /*
                         * Remove other address from default
                         */
                        $changeDefaultFlag = UserAddress::where(['user_id' => $request->user_id, 'is_default' => 1])->where('id', '!=', $model->id);
                        if ($changeDefaultFlag->count())
                        {
                            $changeDefaultFlag = $changeDefaultFlag->update(['is_default' => 0]);
                        }
                    }

                    if ($changeDefaultFlag)
                    {
                        DB::commit();
                        $status       = 'success';
                        $message_text = Lang::get($this->langFile . '.add-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    }
                    else
                    {
                        DB::rollback();
                        $status       = 'error';
                        $message_text = Lang::get($this->langFile . '.add-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                    }
                }
                else
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.add-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
            return redirect()->route('admin.customer.address.index', ['id' => $request->user_id])->with($status, $message_text);
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.error');
        }
        return redirect()->route('admin.customer.index')->with($status, $message_text);
    }

    /*
     * Edit
     */

    public function edit($id)
    {
        $singleRecord = UserAddress::find($id);
        if ($singleRecord)
        {
            $user = User::find($singleRecord->user_id);
            if ($user)
            {
                $isDefault = config('custom.customer_address_default');
                $country   = AdminCountry::getCountryData();
                return view("admin.customer.address.edit", compact('user', 'isDefault', 'country', 'singleRecord'));
            }
        }
        return redirect()->back()->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    /*
     * Update
     */

    public function update(Request $request, $id)
    {
        if (isset($request->user_id) && $request->user_id)
        {
            $model = UserAddress::find($id);
            if ($model)
            {
                /*
                 * Validations
                 */
                $validationRule    = [];
                $validationMessage = [];

                $validationRule['address_title'] = 'required';
                $validationRule['first_name']    = 'required';
                $validationRule['last_name']     = 'required';
                $validationRule['email']         = 'required';
                $validationRule['phone']         = 'required';
                $validationRule['is_default']    = 'required';
                $validationRule['country_id']    = 'required';
                $validationRule['city_id']       = 'sometimes|required';
                $validationRule['city']          = 'sometimes|required';
                //$validationRule['area']          = 'required';
                $validationRule['street']        = 'required';
                $validationRule['house_number']  = 'required';

                $validationMessage['address_title.required'] = Lang::get($this->langFile . '.error-customer-address-req-address_title');
                $validationMessage['first_name.required']    = Lang::get($this->langFile . '.error-customer-address-req-first_name');
                $validationMessage['last_name.required']     = Lang::get($this->langFile . '.error-customer-address-req-last_name');
                $validationMessage['email.required']         = Lang::get($this->langFile . '.error-customer-address-req-email');
                $validationMessage['phone.required']         = Lang::get($this->langFile . '.error-customer-address-req-phone');
                $validationMessage['is_default.required']    = Lang::get($this->langFile . '.error-customer-address-req-is_default');
                $validationMessage['country_id.required']    = Lang::get($this->langFile . '.error-customer-address-req-country_id');
                $validationMessage['city_id.required']       = Lang::get($this->langFile . '.error-customer-address-req-city_id');
                $validationMessage['city.required']          = Lang::get($this->langFile . '.error-customer-address-req-city');
                $validationMessage['area.required']          = Lang::get($this->langFile . '.error-customer-address-req-area');
                $validationMessage['street.required']        = Lang::get($this->langFile . '.error-customer-address-req-street');
                $validationMessage['house_number.required']  = Lang::get($this->langFile . '.error-customer-address-req-house_number');

                $validatedData = $request->validate($validationRule, $validationMessage);

                DB::beginTransaction();

                try
                {
                    $model->user_id       = $request->user_id; //Main Category
                    $model->address_title = $request->address_title;
                    $model->first_name    = $request->first_name;
                    $model->last_name     = $request->last_name;
                    $model->email         = $request->email;
                    $model->phone         = $request->phone;
                    $model->country_id    = $request->country_id;
                    $model->city_id       = null;
                    if (isset($request->city_id))
                    {
                        $model->city_id = $request->city_id;
                    }
                    $model->city = null;
                    if (isset($request->city))
                    {
                        $model->city = $request->city;
                    }
                    //$model->area           = $request->area;
                    $model->street         = $request->street;
                    $model->house_number   = $request->house_number;
                    $model->floor          = $request->floor;
                    $model->appartment     = $request->appartment;
                    $model->address_detail = $request->address_detail;
                    $model->is_default     = $request->is_default;

                    if ($model->save())
                    {
                        $changeDefaultFlag = true;
                        if ($request->is_default == 1)
                        {
                            /*
                             * Remove other address from default
                             */
                            $changeDefaultFlag = UserAddress::where(['user_id' => $request->user_id, 'is_default' => 1])->where('id', '!=', $id);
                            if ($changeDefaultFlag->count())
                            {
                                $changeDefaultFlag = $changeDefaultFlag->update(['is_default' => 0]);
                            }
                        }

                        if ($changeDefaultFlag)
                        {
                            DB::commit();
                            $status       = 'success';
                            $message_text = Lang::get($this->langFile . '.update-success');
                            $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                        }
                        else
                        {
                            DB::rollback();
                            $status       = 'error';
                            $message_text = Lang::get($this->langFile . '.update-error');
                            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                        }
                    }
                    else
                    {
                        DB::rollback();
                        $status       = 'error';
                        $message_text = Lang::get($this->langFile . '.update-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                    }
                }
                catch (Throwable $e)
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
                return redirect()->route('admin.customer.address.index', ['id' => $request->user_id])->with($status, $message_text);
            }
            else
            {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.error');
        }
        return redirect()->route('admin.customer.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        if ($id)
        {
            $record = UserAddress::find($id);

            if ($record)
            {
                DB::beginTransaction();

                try
                {
                    $subDeleteFlag = true;
                    $subRecord     = $record->getCategoryDescription("", true);

                    foreach ($subRecord as $sub)
                    {
                        if (!$sub->delete())
                        {
                            $subDeleteFlag = false;
                        }
                    }

                    /*
                     * Delete Dependecy as well
                     */
                    /*
                     * While Delete
                     */
                    $sortingNumber = $this->getSortingNumber();
                    $sortingNumber = $sortingNumber - 1;
                    $currentOrder  = $record->sorting;
                    $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;
                    $this->updateSortingOrder($currentOrder, $sortingNumber);

                    if ($subDeleteFlag && $record->delete())
                    {
                        DB::commit();
                        $status       = 'success';
                        $message_text = Lang::get($this->langFile . '.delete-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    }
                    else
                    {
                        DB::rollback();
                        $status       = 'error';
                        $message_text = Lang::get($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                    }
                }
                catch (Throwable $e)
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.delete-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
            else
            {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.record_not_found');
            }
            return redirect()->route('admin.product.subcategory.index', ['id' => ''])->with($status, $message_text);
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }
        return redirect()->route('admin.product.category.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = UserAddress::find($id);
        if ($singleRecord && $active == 0 || $active == 1)
        {
            DB::beginTransaction();

            try
            {
                $singleRecord->is_active = $active;
                if ($singleRecord->save())
                {
                    DB::commit();
                    $status       = 'success';
                    $message_text = Lang::get($this->langFile . '.status-change-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    if ($active == 1)
                    {
                        $message_text = str_replace("#status#", '"' . (Lang::get($this->langFile . '.active') . '"'), $message_text);
                    }
                    else
                    {
                        $message_text = str_replace("#status#", '"' . (Lang::get($this->langFile . '.in_active') . '"'), $message_text);
                    }
                }
                else
                {
                    DB::rollback();
                    $status       = 'error';
                    $message_text = Lang::get($this->langFile . '.status-change-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
            return redirect()->route('admin.customer.address.index', ['id' => $singleRecord->user_id])->with($status, $message_text);
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.error');
        }
        return redirect()->route('admin.customer.index')->with($status, $message_text);
    }

    public function getCity(Request $request)
    {
        $countryId = $request->countryID;
        $cityId    = $request->cityID;

        $city = AdminCountry::getCountryCity($countryId);
        return view('admin.customer.address.ajax-city', compact('city', 'countryId'));
    }

}
