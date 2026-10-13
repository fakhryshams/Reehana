<?php

namespace App\Http\Controllers\Admin\Pages\HomeSlider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use App\AdminModel\GeneralSetting\HomeSlider\AdminHomeSlider;
use App\AdminModel\GeneralSetting\HomeSlider\AdminHomeSliderDescription;

/*
 * Other
 */
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class HomeSliderController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.slider';

    /*
     * index function
     */

    public function index(Request $request)
    {
        $sorting               = Helper::getOrderSortingColumn($request);
        $sortKey               = $sorting['sortKey'];
        $sortVal               = $sorting['sortVal'];
        $searchText            = $request->q;
        $pageLength            = $request->pageLength;
        $homeSliderDescription = new AdminHomeSlider();
        $homeSliderDescription = $homeSliderDescription->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.page.home-slider.index', compact('homeSliderDescription', 'searchText', 'sortKey', 'sortVal', 'sorting', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model         = new AdminHomeSlider();
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
            $record = AdminHomeSlider::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = AdminHomeSlider::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting - 1;
                $record->save();
            }
        }
    }

    /*
     * open insert page
     */

    public function create()
    {
        $languageList = Helper::getLanguageList();
        if ($languageList)
        {
            $sortingNumber = $this->getSortingNumber();
            $isActive      = config('admin.product.isActive');
            return view("admin.page.home-slider.add", compact('languageList', 'isActive', 'sortingNumber'));
        }
        else
        {
            return redirect()->route('admin.page.home-slider.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    /*
     * Insert
     */

    public function store(Request $request)
    {
        $languageList         = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule       = [];
        $validationMessage    = [];
        $slider               = "";
        $languageID           = "";
        $sortingNumber        = $this->getSortingNumber();
        $width                = config('custom.banner_image_dimensions_width');
        $height               = config('custom.banner_image_dimensions_height');
        $validationRule       = [
            'link'      => 'required|url',
            'sorting'   => "required|numeric|gte:1|max:$sortingNumber",
            'is_active' => 'required',
            'photo'     => 'required|image|mimes:jpg,jpeg,png|dimensions:width=' . $width . ',height=' . $height,
        ];
        $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'link.required'      => Lang::get($this->langFile . '.error-slider-req-link'),
            'link.url'           => Lang::get($this->langFile . '.error-url-link'),
            'sorting.required'   => Lang::get($this->langFile . '.error-req-sorting'),
            'sorting.numeric'    => Lang::get($this->langFile . '.error-numeric-sorting'),
            'sorting.gte'        => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.min'        => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.max'        => $message_text_sorting,
            'is_active.required' => Lang::get($this->langFile . '.error-slider-req-status'),
            'photo.required'     => Lang::get($this->langFile . '.error-slider-req-photo'),
            'photo.image'        => Lang::get($this->langFile . '.error-slider-image-photo'),
            'photo.mimes'        => Lang::get($this->langFile . '.error-slider-mimes-photo'),
            'photo.dimensions'   => Lang::get($this->langFile . '.error-slider-dimensions-photo'),
        ];
        /*
         * language wise validation
         */
        foreach ($languageList as $language)
        {
            $message_text                                                    = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-slider-req-title'));
         //   $validationRule['title_' . $language->id]                        = 'required';
            $validationMessage['title_' . $language->id . '.required']       = $message_text;
//            $message_text                                                    = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-slider-req-title2'));
//            $validationRule['title2_' . $language->id]                       = 'required';
//            $validationMessage['title2_' . $language->id . '.required']      = $message_text;
//            $message_text                                                    = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-slider-req-description'));
//            $validationRule['description_' . $language->id]                  = 'required';
//            $validationMessage['description_' . $language->id . '.required'] = $message_text;
            $message_text                                                    = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-slider-req-button-text'));
            $validationRule['button_text_' . $language->id]                  = 'required';
            $validationMessage['button_text_' . $language->id . '.required'] = $message_text;
            /*
             * For unique validation
             */
            $slider                                                          = 'title_' . $language->id;
            $languageID                                                      = $language->id;
            /*
             * Unique not required

              $checkUnique                                                     = AdminHomeSliderDescription::where(['banner_header' => $request->$slider, 'language_id' => $languageID])->count();
              if ($checkUnique > 0)
              {
              return redirect()->back()->withInput($request->input)->withErrors([$slider => Lang::get($this->langFile . '.error-slider-unique-name')]);
              }
             * 
             */
        }
        $validatedData = $request->validate($validationRule, $validationMessage);
        DB::beginTransaction();
        try
        { /*
         * Insert status Flag
         */
            $flag  = true;
            $model = new AdminHomeSlider();
            /*
             *  Image
             */
            if ($request->file('photo'))
            {
					$image = $request->file('photo');
					// Generate a WebP file name
					$fileName = uniqid() . '.webp';

					// Define the storage path
					$filePath = config('filepath.home_slider') . '/' . $fileName;

					// Convert the image to WebP
					$webpImage = Image::make($image)->encode('webp', 90); // 90 is the quality

					// Store WebP image in storage
					Storage::disk('local')->put($filePath, $webpImage->stream());

					// Delete the old image
					if ($model->banner_image) {
						Storage::disk('local')->delete($model->banner_image);
					}
					// Save new WebP image path in the database
					$model->banner_image = $filePath;
            }
            /*
             * code for sorting
             */
            $model->sorting     = $request->sorting;
            $this->updateSortingOrder($sortingNumber, $request->sorting);
            /*
             * end of code for sorting
             */
            $model->button_link = $request->link;
            $model->is_active   = $request->is_active;
            /*
             * Save Main Record
             */
            if ($model->save())
            {
                foreach ($languageList as $language)
                {
                    $subModel                       = new AdminHomeSliderDescription();
                    $title                          = 'title_' . $language->id;
                    //$title2                         = 'title2_' . $language->id;
                    $description                    = 'description_' . $language->id;
                    $buttonText                     = 'button_text_' . $language->id;
                    $subModel->banner_management_id = $model->id;
                    $subModel->language_id          = $language->id;
                    $subModel->banner_header        = $request->$title;
                    //$subModel->banner_header2       = $request->$title2;
                    // $subModel->banner_description   = $request->$description;
                    $subModel->button_title         = $request->$buttonText;
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
        return redirect()->route('admin.page.home-slider.index')->with($status, $message_text);
    }

    /*
     * Edit
     */

    public function edit($id)
    {
		
		$singleRecord = AdminHomeSlider::find($id);
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
                $isActive      = config('admin.product.isActive');
                return view("admin.page.home-slider.edit", compact('id', 'isActive', 'languageList', 'singleRecord', 'sortingNumber'));
            }
            else
            {
                return redirect()->route('admin.page.home-slider.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.page.home-slider.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    /*
     * Update
     */

    public function update(Request $request, $id)
    {
        $model = AdminHomeSlider::find($id);
        if ($model)
        {
            /*
             * code for sorting
             */
            /*
             * While Update
             */
            $sortingNumber = $this->getSortingNumber();
            $currentOrder  = $model->sorting;
            $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
            $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;
            $languageList  = Helper::getLanguageList();

            /*
             * Validations
             */
            $validationRule       = [];
            $validationMessage    = [];
            $slider               = "";
            $languageID           = "";
            $width                = config('custom.banner_image_dimensions_width');
            $height               = config('custom.banner_image_dimensions_height');
            $validationRule       = [
                'link'      => 'required|url',
                'sorting'   => "required|numeric|gte:1|max:$sortingNumber",
                'is_active' => 'required',
                'photo'     => 'image|mimes:jpg,jpeg,png|dimensions:width=' . $width . ',height=' . $height,
            ];
            $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'link.required'      => Lang::get($this->langFile . '.error-slider-req-link'),
                'link.url'           => Lang::get($this->langFile . '.error-url-link'),
                'sorting.required'   => Lang::get($this->langFile . '.error-req-sorting'),
                'sorting.numeric'    => Lang::get($this->langFile . '.error-numeric-sorting'),
                'sorting.gte'        => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.min'        => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.max'        => $message_text_sorting,
                'is_active.required' => Lang::get($this->langFile . '.error-slider-req-status'),
                'photo.image'        => Lang::get($this->langFile . '.error-slider-image-photo'),
                'photo.mimes'        => Lang::get($this->langFile . '.error-slider-mimes-photo'),
                'photo.dimensions'   => Lang::get($this->langFile . '.error-slider-dimensions-photo'),
            ];
            /*
             * language wise validation
             */
            foreach ($languageList as $language)
            {
                $message_text                                                    = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-slider-req-title'));
             //   $validationRule['title_' . $language->id]                        = 'required';
                $validationMessage['title_' . $language->id . '.required']       = $message_text;
//                $message_text                                                    = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-slider-req-title2'));
//                $validationRule['title2_' . $language->id]                        = 'required';
//                $validationMessage['title2_' . $language->id . '.required']       = $message_text;
//                $message_text                                                    = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-slider-req-description'));
//                $validationRule['description_' . $language->id]                  = 'required';
//                $validationMessage['description_' . $language->id . '.required'] = $message_text;
                $message_text                                                    = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-slider-req-button-text'));
                $validationRule['button_text_' . $language->id]                  = 'required';
                $validationMessage['button_text_' . $language->id . '.required'] = $message_text;
                /*
                 * For unique validation
                 */
                $slider                                                          = 'title_' . $language->id;
                $languageID                                                      = $language->id;
//                $checkUnique                                                     = AdminHomeSliderDescription::where(['banner_header' => $request->$slider, 'language_id' => $languageID])->where('banner_management_id', '!=', $id)->count();
//                if ($checkUnique > 0)
//                {
//                    return redirect()->back()->withInput($request->input)->withErrors([$slider => Lang::get($this->langFile . '.error-slider-unique-name')]);
//                }
            }
            $validatedData = $request->validate($validationRule, $validationMessage);
            DB::beginTransaction();
            try
            {
                /*
                 *  Image
                 */
               if ($request->file('photo')) {
					// Get the uploaded image
					$image = $request->file('photo');

					// Generate a WebP file name
					$fileName = uniqid() . '.webp';

					// Define the storage path
					$filePath = config('filepath.home_slider') . '/' . $fileName;

					// Convert the image to WebP
					$webpImage = Image::make($image)->encode('webp', 90); // 90 is the quality

					// Store WebP image in storage
					Storage::disk('local')->put($filePath, $webpImage->stream());

					// Delete the old image
					if ($model->banner_image) {
						Storage::disk('local')->delete($model->banner_image);
					}

					// Save new WebP image path in the database
					$model->banner_image = $filePath;
				}
                $model->button_link = $request->link;
                /* sorting during update */
                $model->sorting     = $request->sorting;
                $this->updateSortingOrder($currentOrder, $request->sorting);

                $model->is_active = $request->is_active;
                /*
                 * Save Main Record
                 */
                if ($model->save())
                {
                    /*
                     * Insert status Flag
                     */
                    $flag = true;
                    foreach ($languageList as $language)
                    {
                        $subModel                       = AdminHomeSliderDescription::getDataByLanguageID($id, $language->id);
                        $title                          = 'title_' . $language->id;
                        //$title2                          = 'title2_' . $language->id;
                        $description                    = 'description_' . $language->id;
                        $buttonText                     = 'button_text_' . $language->id;
                        $subModel->banner_management_id = $model->id;
                        $subModel->language_id          = $language->id;
                        $subModel->banner_header        = $request->$title;
                        //$subModel->banner_header2        = $request->$title2;
                        //$subModel->banner_description   = $request->$description;
                        $subModel->button_title         = $request->$buttonText;
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
        return redirect()->route('admin.page.home-slider.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        $bannerCount = AdminHomeSlider::all()->count();
//        dd($bannerCount);
        if ($bannerCount > 1)
        {
            if ($id)
            {
                $record = AdminHomeSlider::find($id);

                if ($record)
                {
                    DB::beginTransaction();

                    try
                    {
                        $subDeleteFlag = true;
                        $subRecord     = $record->getHomeSliderDescription("", true);
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
        }
        else
        {
            $status       = 'error';
            $message_text = Lang::get($this->langFile . '.cannot-delete');
        }
        return redirect()->route('admin.page.home-slider.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminHomeSlider::find($id);
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

            return redirect()->route('admin.page.home-slider.index')->with($status, $message_text);
        }
    }

}
