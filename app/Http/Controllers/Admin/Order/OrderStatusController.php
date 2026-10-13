<?php

namespace App\Http\Controllers\Admin\Order;

use App\AdminModel\Order\AdminOrderStatus;
use App\AdminModel\Order\AdminOrderStatusDescription;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;

class OrderStatusController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.order_status';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting    = Helper::getOrderSortingColumn($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $order      = new AdminOrderStatus();

        $order = $order->getAllData($searchText, $sortKey, $sortVal, $pageLength);

        return view('admin.order.status.index', compact('order', 'sortKey', 'sortVal', 'searchText', 'sorting', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model         = new AdminOrderStatus();
        $sortingNumber = $model->getSortingNumber();
        if (!$sortingNumber)
        {
            $sortingNumber = 0;
        }
        $sortingNumber++;
        return $sortingNumber;
    }

    /*
     * Update Sorting Order
     */

    function updateSortingOrder($currentOrder, $newOrder)
    {
        if ($currentOrder > $newOrder)
        {
            $record = AdminOrderStatus::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = AdminOrderStatus::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting - 1;
                $record->save();
            }
        }
    }

    public function create()
    {
        $sortingNumber = $this->getSortingNumber();
        return view("admin.order.status.add", compact('sortingNumber'));
    }

    public function store(Request $request)
    {
        $languageList = Helper::getLanguageList();

        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];
        $sortingNumber     = $this->getSortingNumber();
        $validationRule    = [
            'sorting'      => "required|numeric|gte:1|max:$sortingNumber",
            'order_status' => "required|unique:admin_orders_status",
        ];

        $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'sorting.required'      => Lang::get($this->langFile . '.error-req-sorting'),
            'order_status.required' => Lang::get($this->langFile . '.error-order-req-status'),
            'order_status.unique'   => Lang::get($this->langFile . '.error-order-unique-name'),
            'sorting.numeric'       => Lang::get($this->langFile . '.error-numeric-sorting'),
            'sorting.gte'           => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.min'           => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.max'           => $message_text_sorting,
        ];

        $validatedData = $request->validate($validationRule, $validationMessage);
        DB::beginTransaction();
        try
        {
            $adminOrderStatus               = new AdminOrderStatus();
            $adminOrderStatus->order_status = $request->order_status;
            $adminOrderStatus->sorting      = $request->sorting;
            $this->updateSortingOrder($sortingNumber, $request->sorting);
            if ($adminOrderStatus->save())
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
        catch (Throwable $e)
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }
        return redirect()->route('admin.order.status.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $orderStatus = AdminOrderStatus::find($id);
        if ($orderStatus)
        {
            /*
             * While Update
             */
            $sortingNumber = $this->getSortingNumber();
            $currentOrder  = $orderStatus->sorting;
            $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
            return view("admin.order.status.edit", compact('orderStatus', 'sortingNumber'));
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.edit-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            return redirect()->route('admin.order.status.index')->with($status, $message_text);
        }
    }

    public function update(Request $request, $id)
    {
        $model = AdminOrderStatus::find($id);
        if ($model)
        {
            $validationRule    = [];
            $validationMessage = [];
            /*
             * While Update
             */
            $sortingNumber     = $this->getSortingNumber();
            $currentOrder      = $model->sorting;
            $sortingNumber     = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
            $currentOrder      = $currentOrder ? $currentOrder : $sortingNumber;
            $validationRule    = [
                'sorting'      => "required|numeric|gte:1|max:$sortingNumber",
                'order_status' => "required|unique:admin_orders_status,order_status," . $id,
            ];

            $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'sorting.required'      => Lang::get($this->langFile . '.error-req-sorting'),
                'order_status.required' => Lang::get($this->langFile . '.error-order-req-status'),
                'order_status.unique'   => Lang::get($this->langFile . '.error-order-unique-name'),
                'sorting.numeric'       => Lang::get($this->langFile . '.error-numeric-sorting'),
                'sorting.gte'           => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.min'           => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.max'           => $message_text_sorting,
            ];

            $validatedData = $request->validate($validationRule, $validationMessage);

            try
            {
                DB::beginTransaction();
                $model->sorting      = $request->sorting;
                $this->updateSortingOrder($currentOrder, $request->sorting);
                $model->order_status = $request->order_status;
                if ($model->save())
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
            catch (Throwable $e)
            {
                DB::rollback();
                $status       = 'error';
                $message_text = Lang::get($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.order.status.index')->with($status, $message_text);
    }

    /*
     * delete
     */

    public function delete($id)
    {
        $titleCount = AdminOrderStatus::all()->count();
        if ($titleCount > 1)
        {
            if ($id)
            {
                $record = AdminOrderStatus::find($id);

                if ($record)
                {
                    DB::beginTransaction();

                    try
                    {
                        if ($record->delete())
                        {
                            /*
                             * While Delete Change Sorting
                             */
                            $sortingNumber = $this->getSortingNumber();
                            $sortingNumber = $sortingNumber - 1;
                            $currentOrder  = $record->sorting;
                            $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;
                            $this->updateSortingOrder($currentOrder, $sortingNumber);

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
            $message_text = Lang::get($this->langFile . '.cannot-delete');
        }
        return redirect()->route('admin.order.status.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminOrderStatus::find($id);
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

            return redirect()->route('admin.order.status.index')->with($status, $message_text);
        }
    }

}
