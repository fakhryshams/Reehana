<?php

namespace App\Http\Controllers\Admin\Monogram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\AdminModel\Monogram\MonogramFontStyle;
use App\AdminModel\Monogram\MonogramLetterCount;
use App\AdminModel\Monogram\MonogramPlacement;
use App\AdminModel\Monogram\MonogramColor;
use App\AdminModel\Monogram\MonogramMaster;
use App\AdminModel\Monogram\MonogramImage;
use DB;
use Illuminate\Support\Facades\Storage;

class MonogramFontStyleController extends Controller
{

    private $langFile = 'admin';
    private $module = '.monogram_font_style';

    public function index(Request $request)
    {
        /*
         * Get SortKey and SortOrder from helper
         */
        $sorting = Helper::getOrderSortingColumn($request);
        $sortKey = $sorting['sortKey'];
        $sortVal = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $languageList = Helper::getLanguageList();
        $model = new MonogramFontStyle();
        $model = $model->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.monogram.font-style.index', compact('model', 'sortKey', 'sortVal', 'searchText', 'languageList', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model = new MonogramFontStyle();
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
            $record = MonogramFontStyle::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = MonogramFontStyle::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
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
            $isActive = config('admin.product.isActive');
            $booleanDD = config('admin.product.isFeature');
            $sortingNumber = $this->getSortingNumber();

            $initials = MonogramLetterCount::all();
            $placements = MonogramPlacement::where("is_active", 1)->orderBy('sorting', 'asc')->get();
            $colors = MonogramColor::where("is_active", 1)->orderBy('sorting', 'asc')->get();
            $alphabets = config('custom.alphabets');
            return view("admin.monogram.font-style.add", compact('languageList', 'isActive', 'sortingNumber', 'booleanDD', 'initials', 'placements', 'colors', 'alphabets'));
        }
        else
        {
            return redirect()->route('admin.monogram.font-style.index')->with('error', __($this->langFile . 'language-not-found'));
        }
    }

    public function store(Request $request)
    {
//        dd($request->all());
        $languageList = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule = [];
        $validationMessage = [];
        $colorName = "";
        $languageID = "";

        $sortingNumber = $this->getSortingNumber();
        $validationRule = [
            'sorting' => "required|numeric|gte:1|max:$sortingNumber",
            'style_name' => "required|unique:admin_monogram_font_style,style_name,NULL,id,deleted_at,NULL",
            'style_image' => 'required|image|mimes:png|dimensions:width=' . config('custom.monogram_style_image_dimensions_width') . ',height=' . config('custom.monogram_style_image_dimensions_height'),
            'style_image_svg' => 'required|mimes:svg|dimensions:width=' . config('custom.monogram_style_image_dimensions_width') . ',height=' . config('custom.monogram_style_image_dimensions_height'),
        ];

        $message_text_sorting = __($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'sorting.required' => __($this->langFile . '.error-req-sorting'),
            'sorting.numeric' => __($this->langFile . '.error-numeric-sorting'),
            'sorting.gte' => __($this->langFile . '.error-min-sorting'),
            'sorting.min' => __($this->langFile . '.error-min-sorting'),
            'sorting.max' => $message_text_sorting,
            'style_name.required' => __($this->langFile . '.error-req-style_name'),
            'style_name.unique' => __($this->langFile . '.error-unique-style_name'),
            'style_image.required' => __($this->langFile . '.error-req-style_image'),
            'style_image.image' => __($this->langFile . '.error-image-style_image'),
            'style_image.mimes' => __($this->langFile . '.error-mimes-style_image'),
            'style_image.dimensions' => __($this->langFile . '.error-dimensions-style_image'),
            'style_image_svg.required' => __($this->langFile . '.error-req-style_image'),
            'style_image_svg.image' => __($this->langFile . '.error-image-style_image'),
            'style_image_svg.mimes' => __($this->langFile . '.error-mimes-style_image'),
            'style_image_svg.dimensions' => __($this->langFile . '.error-dimensions-style_image'),
        ];
        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();

        try
        {
            /*
             * Insert status Flag
             */
            $flag = true;
            $model = new MonogramFontStyle();

            /*
             * code for sorting
             */
            $model->sorting = $request->sorting;
            $model->style_name = $request->style_name;
            $model->has_varient = $request->has_varient;

//            $model->has_preview = $model->has_varient == 1 ? 0 : 1;
             $model->has_preview = 1;
            // $model->has_preview = 0;

            if ($request->style_image)
            {
                $file = $request->style_image;
                $path = $this->uploadImage($file);

                if ($path)
                {
                    $model->style_image = $path;
                }
            }

            if ($request->style_image_svg)
            {
                $file = $request->style_image_svg;
                $path = $this->uploadImage($file);

                if ($path)
                {
                    $model->style_image_svg = $path;
                }
            }

            $this->updateSortingOrder($sortingNumber, $request->sorting);


            /*
             * end of code for sorting
             */
            /*
             * save color
             */
            if ($model->save())
            {
                $slugFlag = false;

                if ($slugFlag == false)
                {
                    $slugFlag = true;
                    $slug = $request->style_name;
                    $oldSlug = $request->style_name;

                    $slugCount = 0;
                    do
                    {
                        if ($slugCount == 0)
                        {
                            $currentSlug = Helper::slugify($oldSlug);
                        }
                        else
                        {
                            $currentSlug = Helper::slugify($oldSlug . '-' . $slugCount);
                        }

                        $slug = $currentSlug;
                        $checkSlug = MonogramFontStyle::checkSlug($currentSlug);
                        $slugCount++;
                    }
                    while (count($checkSlug) > 0);

                    $updateSlug = MonogramFontStyle::updateSlug($model->id, $slug);
                    if (!$updateSlug)
                    {
                        $flag = false;
                    }
                }

                if (isset($request->initials))
                {
                    foreach ($request->initials as $initials)
                    {
                        $monogramMaster = new MonogramMaster();
                        $monogramMaster->font_style_id = $model->id;
                        $monogramMaster->type = "initials";
                        $monogramMaster->value1 = $initials;
                        $monogramMaster->save();
                    }
                }

                if (isset($request->placements))
                {
                    foreach ($request->placements as $placement)
                    {
                        $monogramMaster = new MonogramMaster();
                        $monogramMaster->font_style_id = $model->id;
                        $monogramMaster->type = "placement";
                        $monogramMaster->value1 = $placement;
                        $monogramMaster->save();
                    }
                }

                if (isset($request->colors))
                {
                    foreach ($request->colors as $color)
                    {
                        $monogramMaster = new MonogramMaster();
                        $monogramMaster->font_style_id = $model->id;
                        $monogramMaster->type = "color";
                        $monogramMaster->value1 = $color;
                        $monogramMaster->save();
                    }
                }

                $alphabets = config('custom.alphabets');
                #Left
                foreach ($alphabets as $letter)
                {
                    $imageName = "left_" . $letter;
                    if (isset($request->$imageName))
                    {
                        $fileName = "Left_" . $letter;
                        $file = $request->$imageName;
                        $path = $this->uploadLetterImage($file, 'public/monogram/' . $slug, $fileName);

                        $image = MonogramImage::where(['font_style_id' => $model->id, 'letter' => $letter])->first();
                        if (!$image)
                        {
                            $image = new MonogramImage();
                        }

                        $image->font_style_id = $model->id;
                        $image->letter = $letter;
                        $image->left_image = $path;
                        $image->save();
                    }
                }

                #Center
                foreach ($alphabets as $letter)
                {
                    $imageName = "center_" . $letter;
                    if (isset($request->$imageName))
                    {
                        $fileName = "Center_" . $letter;
                        $file = $request->$imageName;
                        $path = $this->uploadLetterImage($file, 'public/monogram/' . $slug, $fileName);
                        $image = MonogramImage::where(['font_style_id' => $model->id, 'letter' => $letter])->first();
                        if (!$image)
                        {
                            $image = new MonogramImage();
                        }

                        $image->font_style_id = $model->id;
                        $image->letter = $letter;
                        $image->center_image = $path;
                        $image->save();
                    }
                }
                #Right
                foreach ($alphabets as $letter)
                {
                    $imageName = "right_" . $letter;
                    if (isset($request->$imageName))
                    {
                        $fileName = "Right_" . $letter;
                        $file = $request->$imageName;
                        $path = $this->uploadLetterImage($file, 'public/monogram/' . $slug, $fileName);
                        $image = MonogramImage::where(['font_style_id' => $model->id, 'letter' => $letter])->first();
                        if (!$image)
                        {
                            $image = new MonogramImage();
                        }

                        $image->font_style_id = $model->id;
                        $image->letter = $letter;
                        $image->right_image = $path;
                        $image->save();
                    }
                }
                #Right
                foreach ($alphabets as $letter)
                {
                    $imageName = "single_" . $letter;
                    if (isset($request->$imageName))
                    {
                        $fileName = $slug . "_" . $letter;
                        $file = $request->$imageName;
                        $path = $this->uploadLetterImage($file, 'public/monogram/' . $slug, $fileName);
                        $image = MonogramImage::where(['font_style_id' => $model->id, 'letter' => $letter])->first();
                        if (!$image)
                        {
                            $image = new MonogramImage();
                        }

                        $image->font_style_id = $model->id;
                        $image->letter = $letter;
                        $image->single_image = $path;
                        $image->save();
                    }
                }

                DB::commit();
                $status = 'success';
                $message_text = __($this->langFile . '.add-success');
                $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
            }
            else
            {
                DB::rollback();
                $status = 'error';
                $message_text = __($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
            }
        } catch (Throwable $e)
        {
            DB::rollback();
            $status = 'error';
            $message_text = __($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.monogram.font-style.index')->with($status, $message_text);
    }

    public function edit($id)
    {
        $singleRecord = MonogramFontStyle::find($id);
        if ($singleRecord)
        {
            $languageList = Helper::getLanguageList();
            if ($languageList)
            {
                /*
                 * While Update
                 */
                $booleanDD = config('admin.product.isFeature');
                $sortingNumber = $this->getSortingNumber();
                $currentOrder = $singleRecord->sorting;
                $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;

                $initials = MonogramLetterCount::all();
                $savedInitials = MonogramMaster::where(['font_style_id' => $id, 'type' => 'initials', 'is_active' => 1])->get();
                $savedInitialsValues = [];
                if ($savedInitials)
                {
                    $savedInitialsValues = $savedInitials->pluck('value1')->toArray();
                }

                $placements = MonogramPlacement::orderBy('sorting', 'asc')->get();
                $savedPlacement = MonogramMaster::where(['font_style_id' => $id, 'type' => 'placement', 'is_active' => 1])->get();
                $savedPlacementValues = [];
                if ($savedPlacement)
                {
                    $savedPlacementValues = $savedPlacement->pluck('value1')->toArray();
                }
                $colors = MonogramColor::where("is_active", 1)->orderBy('sorting', 'asc')->get();
                $savedColor = MonogramMaster::where(['font_style_id' => $id, 'type' => 'color', 'is_active' => 1])->get();
                $savedColorValues = [];
                if ($savedColor)
                {
                    $savedColorValues = $savedColor->pluck('value1')->toArray();
                }
                $savedImages = MonogramImage::where(['font_style_id' => $id, 'is_active' => 1])->get();

                $alphabets = config('custom.alphabets');

                return view("admin.monogram.font-style.edit", compact('id', 'languageList', 'singleRecord', 'sortingNumber', 'booleanDD', 'initials', 'placements', 'colors', 'alphabets', 'savedInitialsValues', 'savedPlacementValues', 'savedColorValues', 'savedImages'));
            }
            else
            {
                return redirect()->route('admin.monogram.font-style.index')->with('error', __($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.monogram.font-style.index')->with('error', __($this->langFile . '.record_not_found'));
    }

    public function update(Request $request, $id)
    {
        //dd($request->all());
        $model = MonogramFontStyle::find($id);
        if ($model)
        {
            $languageList = Helper::getLanguageList();
            /*
             * Validations
             */
            $validationRule = [];
            $validationMessage = [];

            /*
             * While Update
             */
            $sortingNumber = $this->getSortingNumber();
            $currentOrder = $model->sorting;
            $sortingNumber = $currentOrder ? ($sortingNumber - 1) : $sortingNumber;
            $currentOrder = $currentOrder ? $currentOrder : $sortingNumber;

            $colorName = "";
            $languageID = "";
            $validationRule = [
                'sorting' => "required|numeric|gte:1|max:$sortingNumber",
                'style_name' => "required|unique:admin_monogram_font_style,style_name,$id,id,deleted_at,NULL",
                'style_image' => 'nullable|image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.monogram_style_image_dimensions_width') . ',height=' . config('custom.monogram_style_image_dimensions_height'),
            ];

            $message_text_sorting = __($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'sorting.required' => __($this->langFile . '.error-req-sorting'),
                'sorting.numeric' => __($this->langFile . '.error-numeric-sorting'),
                'sorting.gte' => __($this->langFile . '.error-min-sorting'),
                'sorting.min' => __($this->langFile . '.error-min-sorting'),
                'sorting.max' => $message_text_sorting,
                'style_name.required' => __($this->langFile . '.error-req-style_name'),
                'style_name.unique' => __($this->langFile . '.error-unique-style_name'),
                'style_image.required' => __($this->langFile . '.error-req-style_image'),
                'style_image.image' => __($this->langFile . '.error-image-style_image'),
                'style_image.mimes' => __($this->langFile . '.error-mimes-style_image'),
                'style_image.dimensions' => __($this->langFile . '.error-dimensions-style_image'),
            ];

            $validatedData = $request->validate($validationRule, $validationMessage);

            DB::beginTransaction();

            try
            {
                /*
                 * save color
                 */
                $model->sorting = $request->sorting;
                $model->style_name = $request->style_name;
                $model->has_varient = $request->has_varient;

//                $model->has_preview = $model->has_varient == 1 ? 0 : 1;
                $model->has_preview = 1;
            // $model->has_preview = 0;

                $oldImage = false;
                if ($request->style_image && $request->style_image)
                {
                    $file = $request->style_image;
                    $path = $this->uploadImage($file);

                    if ($path)
                    {
                        $oldImage = $model->style_image;
                        $model->style_image = $path;
                    }
                }
                if ($request->style_image_svg)
                {
                    $file = $request->style_image_svg;
                    $path = $this->uploadImage($file);

                    if ($path)
                    {
                        $model->style_image_svg = $path;
                    }
                }
                $this->updateSortingOrder($currentOrder, $request->sorting);

                ////
                if ($model->save())
                {
                    /*
                     * Unlink Previous Image
                     */
                    if ($oldImage)
                    {
                        if (Storage::has($oldImage))
                        {
                            Storage::delete($oldImage);
                        }
                    }

                    $slug = $model->style_slug;

                    if (isset($request->initials))
                    {
                        MonogramMaster::where(['font_style_id' => $model->id, 'type' => 'initials'])->delete();
                        foreach ($request->initials as $initials)
                        {
                            $monogramMaster = new MonogramMaster();
                            $monogramMaster->font_style_id = $model->id;
                            $monogramMaster->type = "initials";
                            $monogramMaster->value1 = $initials;
                            $monogramMaster->save();
                        }
                    }

                    if (isset($request->placements))
                    {
                        MonogramMaster::where(['font_style_id' => $model->id, 'type' => 'placement'])->delete();
                        foreach ($request->placements as $placement)
                        {
                            $monogramMaster = new MonogramMaster();
                            $monogramMaster->font_style_id = $model->id;
                            $monogramMaster->type = "placement";
                            $monogramMaster->value1 = $placement;
                            $monogramMaster->save();
                        }
                    }

                    if (isset($request->colors))
                    {
                        MonogramMaster::where(['font_style_id' => $model->id, 'type' => 'color'])->delete();
                        foreach ($request->colors as $color)
                        {
                            $monogramMaster = new MonogramMaster();
                            $monogramMaster->font_style_id = $model->id;
                            $monogramMaster->type = "color";
                            $monogramMaster->value1 = $color;
                            $monogramMaster->save();
                        }
                    }

                    $alphabets = config('custom.alphabets');
                    #Left
                    foreach ($alphabets as $letter)
                    {
                        $imageName = "left_" . $letter;
                        if (isset($request->$imageName))
                        {
                            $fileName = "Left_" . $letter;
                            $file = $request->$imageName;
                            $path = $this->uploadLetterImage($file, 'public/monogram/' . $slug, $fileName);

                            $image = MonogramImage::where(['font_style_id' => $model->id, 'letter' => $letter])->first();
                            if (!$image)
                            {
                                $image = new MonogramImage();
                            }
                            else
                            {
                                if ($path)
                                {
                                    $oldImage = $image->left_image;
                                    if ($oldImage)
                                    {
                                        if (Storage::has($oldImage))
                                        {
                                            //Storage::delete($oldImage);
                                        }
                                    }
                                }
                            }

                            $image->font_style_id = $model->id;
                            $image->letter = $letter;
                            $image->left_image = $path;
                            $image->save();
                        }
                    }
                    #Center
                    foreach ($alphabets as $letter)
                    {
                        $imageName = "center_" . $letter;
                        if (isset($request->$imageName))
                        {
                            $fileName = "Center_" . $letter;
                            $file = $request->$imageName;
                            $path = $this->uploadLetterImage($file, 'public/monogram/' . $slug, $fileName);
                            $image = MonogramImage::where(['font_style_id' => $model->id, 'letter' => $letter])->first();
                            if (!$image)
                            {
                                $image = new MonogramImage();
                            }
                            else
                            {
                                if ($path)
                                {
                                    $oldImage = $image->center_image;
                                    if ($oldImage)
                                    {
                                        if (Storage::has($oldImage))
                                        {
                                            //Storage::delete($oldImage);
                                        }
                                    }
                                }
                            }
                            $image->font_style_id = $model->id;
                            $image->letter = $letter;
                            $image->center_image = $path;
                            $image->save();
                        }
                    }
                    #Right
                    foreach ($alphabets as $letter)
                    {
                        $imageName = "right_" . $letter;
                        if (isset($request->$imageName))
                        {
                            $fileName = "Right_" . $letter;
                            $file = $request->$imageName;
                            $path = $this->uploadLetterImage($file, 'public/monogram/' . $slug, $fileName);
                            $image = MonogramImage::where(['font_style_id' => $model->id, 'letter' => $letter])->first();
                            if (!$image)
                            {
                                $image = new MonogramImage();
                            }
                            else
                            {
                                if ($path)
                                {
                                    $oldImage = $image->right_image;
                                    if ($oldImage)
                                    {
                                        if (Storage::has($oldImage))
                                        {
                                            //Storage::delete($oldImage);
                                        }
                                    }
                                }
                            }
                            $image->font_style_id = $model->id;
                            $image->letter = $letter;
                            $image->right_image = $path;
                            $image->save();
                        }
                    }
                    #Right
                    foreach ($alphabets as $letter)
                    {
                        $imageName = "single_" . $letter;
                        if (isset($request->$imageName))
                        {
                            $fileName = $slug . "_" . $letter;
                            $file = $request->$imageName;
                            $path = $this->uploadLetterImage($file, 'public/monogram/' . $slug, $fileName);
                            $image = MonogramImage::where(['font_style_id' => $model->id, 'letter' => $letter])->first();
                            if (!$image)
                            {
                                $image = new MonogramImage();
                            }
                            else
                            {
                                if ($path)
                                {
                                    $oldImage = $image->single_image;
                                    if ($oldImage)
                                    {
                                        if (Storage::has($oldImage))
                                        {
                                            //Storage::delete($oldImage);
                                        }
                                    }
                                }
                            }
                            $image->font_style_id = $model->id;
                            $image->letter = $letter;
                            $image->single_image = $path;
                            $image->save();
                        }
                    }

                    DB::commit();
                    $status = 'success';
                    $message_text = __($this->langFile . '.update-success');
                    $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
                }
                else
                {
                    DB::rollback();
                    $status = 'error';
                    $message_text = __($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                }
            } catch (Throwable $e)
            {
                DB::rollback();
                $status = 'error';
                $message_text = __($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
            }
        }
        else
        {
            DB::rollback();
            $status = 'error';
            $message_text = __($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
        }
        return redirect()->route('admin.monogram.font-style.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        if ($id)
        {
            $record = MonogramFontStyle::find($id);

            if ($record)
            {
                DB::beginTransaction();
                try
                {
                    /*
                     * While Delete Change Sorting
                     */
                    $oldImage = $record->style_image;

                    $sortingNumber = $this->getSortingNumber();
                    $sortingNumber = $sortingNumber - 1;
                    $currentOrder = $record->sorting;
                    $currentOrder = $currentOrder ? $currentOrder : $sortingNumber;
                    $this->updateSortingOrder($currentOrder, $sortingNumber);

                    if ($record->delete())
                    {
                        DB::commit();

                        if ($oldImage)
                        {
                            if (Storage::has($oldImage))
                            {
                                Storage::delete($oldImage);
                            }
                        }

                        $status = 'success';
                        $message_text = __($this->langFile . '.delete-success');
                        $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
                    }
                    else
                    {
                        DB::rollback();
                        $status = 'error';
                        $message_text = __($this->langFile . '.delete-error');
                        $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                    }
                } catch (Throwable $e)
                {
                    DB::rollback();
                    $status = 'error';
                    $message_text = __($this->langFile . '.delete-error');
                    $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                }
            }
            else
            {
                $status = 'error';
                $message_text = __($this->langFile . '.record_not_found');
            }
        }
        else
        {
            $status = 'error';
            $message_text = __($this->langFile . '.record_not_found');
        }

        return redirect()->route('admin.monogram.font-style.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = MonogramFontStyle::find($id);
        if ($singleRecord && $active == 0 || $active == 1)
        {
            DB::beginTransaction();

            try
            {
                $singleRecord->is_active = $active;
                if ($singleRecord->save())
                {
                    DB::commit();
                    $status = 'success';
                    $message_text = __($this->langFile . '.status-change-success');
                    $message_text = str_replace("#module#", __($this->langFile . $this->module), $message_text);
                    if ($active == 1)
                    {
                        $message_text = str_replace("#status#", '"' . (__($this->langFile . '.active') . '"'), $message_text);
                    }
                    else
                    {
                        $message_text = str_replace("#status#", '"' . (__($this->langFile . '.in_active') . '"'), $message_text);
                    }
                }
                else
                {
                    DB::rollback();
                    $status = 'error';
                    $message_text = __($this->langFile . '.status-change-error');
                    $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
                }
            } catch (Throwable $e)
            {
                DB::rollback();
                $status = 'error';
                $message_text = __($this->langFile . '.status-change-error');
                $message_text = str_replace("#module#", strtolower(__($this->langFile . $this->module)), $message_text);
            }

            return redirect()->route('admin.monogram.font-style.index')->with($status, $message_text);
        }
    }

    public function uploadImage($file)
    {
        // Get filename with extension
        $filenameWithExt = $file->getClientOriginalName();

        // Get file path
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

        // Remove unwanted characters
        $filename = preg_replace("/[^A-Za-z0-9 ]/", '', $filename);
        $filename = preg_replace("/\s+/", '-', $filename);

        // Get the original image extension
        $extension = $file->getClientOriginalExtension();

        // Create unique file name
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

        $path = $file->storeAs(config('filepath.monogram_font_style_image'), $fileNameToStore);

        return $path;
    }

    public function uploadLetterImage($file, $filePath, $filename)
    {
        // Get filename with extension
        $filenameWithExt = $file->getClientOriginalName();

        // Get file path
        //$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        // Remove unwanted characters
        //$filename = preg_replace("/[^A-Za-z0-9 ]/", '', $filename);
        //$filename = preg_replace("/\s+/", '-', $filename);
        // Get the original image extension
        $extension = $file->getClientOriginalExtension();

        // Create unique file name
        $fileNameToStore = $filename . '.' . $extension;

        $path = $file->storeAs($filePath, $fileNameToStore);

        return $path;
    }

    public function getImageView(Request $request)
    {
        $savedImages = isset($request->savedImages) ? json_decode($request->savedImages) : [];
//        dd(json_decode($request->savedImages));
        $hasVarient = $request->hasVarient;
        $initialsVal = $request->initialsVal;
        $isImageRequired = isset($request->isImageRequired) ? $request->isImageRequired : true;

        $alphabets = config('custom.alphabets');

        if (!$hasVarient)
        {
            if ($initialsVal)
            {
                return \View::make('admin.monogram.font-style.ajax-single', compact('alphabets', 'savedImages', 'isImageRequired'))->render();
            }
        }
        else
        {
            if ($initialsVal)
            {
                if (in_array("4", $initialsVal))
                {
                    return \View::make('admin.monogram.font-style.ajax-all-3', compact('alphabets', 'savedImages', 'isImageRequired'))->render();
                }
                elseif (in_array("3", $initialsVal))
                {
                    return \View::make('admin.monogram.font-style.ajax-all-3', compact('alphabets', 'savedImages', 'isImageRequired'))->render();
                }
                else if (in_array("2", $initialsVal))
                {
                    return \View::make('admin.monogram.font-style.ajax-left-right', compact('alphabets', 'savedImages', 'isImageRequired'))->render();
                }
                else if (in_array("1", $initialsVal))
                {
                    return \View::make('admin.monogram.font-style.ajax-center', compact('alphabets', 'savedImages', 'isImageRequired'))->render();
                }
            }
        }
        return false;
    }

}
