<?php

namespace App\Http\Controllers\Admin\GeneralSetting\PaymentGateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\UserModel\PaymentMethod;
use App\UserModel\PaymentMethodDescription;
use App\AdminModel\GeneralSetting\FAQ\FaqDescription;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;

class PaymentGatewayController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.payment-gateway';

    /*
     * index function
     */

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting       = Helper::getOrderSortingColumn($request);
        $sortKey       = $sorting['sortKey'];
        $sortVal       = $sorting['sortVal'];
        $searchText    = $request->q;
        $pageLength    = $request->pageLength;
        $languageList  = Helper::getLanguageList();
        $paymentMethod = new PaymentMethod();
        $paymentMethod = $paymentMethod->getAllData($searchText, $sortKey, $sortVal, $pageLength);

        return view('admin.general-setting.payment-method.index', compact('paymentMethod', 'sortKey', 'sortVal', 'searchText', 'languageList', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model         = new Faq();
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
            $record = HomeCategorySection::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = HomeCategorySection::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting - 1;
                $record->save();
            }
        }
    }

    public function create()
    {
        $languageList = Helper::getLanguageList();
        if ($languageList)
        {
            $sortingNumber = $this->getSortingNumber();
            return view("admin.general-setting.faq.add", compact('languageList', 'sortingNumber'));
        }
        else
        {
            return redirect()->route('admin.general-setting.faq.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    public function store(Request $request)
    {
        $languageList      = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        $question   = "";
        $answer     = "";
        $languageID = "";

        $sortingNumber  = $this->getSortingNumber();
        $validationRule = [
            'sorting' => "required|numeric|gte:1|max:$sortingNumber",
        ];

        $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'sorting.required' => Lang::get($this->langFile . '.error-req-sorting'),
            'sorting.numeric'  => Lang::get($this->langFile . '.error-numeric-sorting'),
            'sorting.gte'      => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.min'      => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.max'      => $message_text_sorting,
        ];
        /*
         * language wise validation
         */
        foreach ($languageList as $language)
        {
            $message_text                                                 = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-faq-req-question'));
            $validationRule['question_' . $language->id]                  = 'required';
            $validationMessage['question_' . $language->id . '.required'] = $message_text;
            $message_text                                                 = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-faq-req-answer'));
            $validationRule['answer_' . $language->id]                    = 'required';
            $validationMessage['answer_' . $language->id . '.required']   = $message_text;
        }

        $validatedData = $request->validate($validationRule, $validationMessage);
        DB::beginTransaction();
        try
        {
            $model          = new Faq();
            /*
             * code for sorting
             */
            $model->sorting = $request->sorting;
            $this->updateSortingOrder($sortingNumber, $request->sorting);
            /*
             * end of code for sorting
             */
            /*
             * Save
             */
            if ($model->save())
            {
                /*
                 * Insert status Flag
                 */
                $flag = true;

                foreach ($languageList as $language)
                {
                    $subModel              = new FaqDescription();
                    $question              = 'question_' . $language->id;
                    $answer                = 'answer_' . $language->id;
                    $subModel->faq_id      = $model->id;
                    $subModel->question    = $request->$question;
                    $subModel->answer      = $request->$answer;
                    $subModel->language_id = $language->id;

                    if (!$subModel->save())
                    {
                        $flag = false;
                    }
                }

                if ($flag)
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

        return redirect()->route('admin.general-setting.faq.index')->with($status, $message_text);
    }

    /*
     * Edit
     */

    public function edit($id)
    {
        $singleRecord = Faq::find($id);
        if ($singleRecord)
        {
            $languageList = Helper::getLanguageList();
            if ($languageList)
            {
                /*
                 * While Update
                 */
                $sortingNumber = $this->getSortingNumber();
                $currentOrder  = $singleRecord->sorting;
                $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;

                return view("admin.general-setting.faq.edit", compact('id', 'languageList', 'singleRecord', 'sortingNumber'));
            }
            else
            {
                $status       = 'error';
                $message_text = Lang::get($this->langFile . 'language-not-found');
            }
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . 'record_not_found');
        }
        return redirect()->route('admin.general-setting.faq.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    public function update(Request $request, $id)
    {
        $model = Faq::find($id);
        if ($model)
        {
            $languageList      = Helper::getLanguageList();
            /*
             * Validations
             */
            $validationRule    = [];
            $validationMessage = [];

            $question   = "";
            $answer     = "";
            $languageID = "";

            /*
             * While Update
             */
            $sortingNumber = $this->getSortingNumber();
            $currentOrder  = $model->sorting;
            $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
            $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;

            $validationRule = [
                'sorting' => "required|numeric|gte:1|max:$sortingNumber",
            ];

            $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'sorting.required' => Lang::get($this->langFile . '.error-req-sorting'),
                'sorting.numeric'  => Lang::get($this->langFile . '.error-numeric-sorting'),
                'sorting.gte'      => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.min'      => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.max'      => $message_text_sorting,
            ];
            /*
             * language wise validation
             */
            foreach ($languageList as $language)
            {
                $message_text                                                 = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-faq-req-question'));
                $validationRule['question_' . $language->id]                  = 'required';
                $validationMessage['question_' . $language->id . '.required'] = $message_text;
                $message_text                                                 = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-faq-req-answer'));
                $validationRule['answer_' . $language->id]                    = 'required';
                $validationMessage['answer_' . $language->id . '.required']   = $message_text;
            }

            $validatedData = $request->validate($validationRule, $validationMessage);

            DB::beginTransaction();
            try
            {
                $model->sorting = $request->sorting;
                $this->updateSortingOrder($currentOrder, $request->sorting);

                /*
                 * Save Main Record
                 */
                if ($model->save())
                {
                    $flag = true;
                    foreach ($languageList as $language)
                    {
                        $subModel = FaqDescription::getDataByLanguageID($id, $language->id);
                        $question = 'question_' . $language->id;
                        $answer   = 'answer_' . $language->id;

                        $subModel->question = $request->$question;
                        $subModel->answer   = $request->$answer;

                        if (!$subModel->save())
                        {
                            $flag = false;
                        }
                    }

                    if ($flag)
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
        }
        else
        {
            DB::rollback();
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }
        return redirect()->route('admin.general-setting.faq.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {

        if ($id)
        {
            $record = Faq::find($id);

            if ($record)
            {
                DB::beginTransaction();
                try
                {
                    $subDeleteFlag = true;
                    $subRecord     = $record->getDescription("", true);
                    foreach ($subRecord as $sub)
                    {
                        if (!$sub->delete())
                        {
                            $subDeleteFlag = false;
                        }
                    }

                    /*
                     * While Delete Change Sorting
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
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.record_not_found');
        }

        return redirect()->route('admin.general-setting.faq.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = PaymentMethod::find($id);
        if ($singleRecord && ($active == 0 || $active == 1) && $singleRecord->can_edit)
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

            return redirect()->route('admin.general-setting.payment-gateway.index')->with($status, $message_text);
        }
    }

}
