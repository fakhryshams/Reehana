<?php

namespace App\Http\Controllers\Admin\Pages\OurCollection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Helper;
use DB;
use Lang;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use App\AdminModel\AdminOurCollection;
use App\AdminModel\AdminOurCollectionDescription;


class OurCollectionController extends Controller
{

    private $langFile = 'admin';
    private $module   = '.our-collection';

    /*
     * index function
     */

    public function index(Request $request)
    {
        $sorting    = Helper::getOrderSortingColumn($request);
        $sortKey    = $sorting['sortKey'];
        $sortVal    = $sorting['sortVal'];
        $searchText = $request->q;
        $pageLength = $request->pageLength;
        $data       = new AdminOurCollection();
        $data       = $data->getAllData($searchText, $sortKey, $sortVal, $pageLength);
        return view('admin.page.our-collection.index', compact('data', 'searchText', 'sortKey', 'sortVal', 'sorting', 'pageLength'));
    }

    function getSortingNumber()
    {
        $model         = new AdminOurCollection();
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
            $record = AdminOurCollection::where('sorting', '>=', $newOrder)->where('sorting', '<=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
            foreach ($record as $record)
            {
                $record->sorting = $record->sorting + 1;
                $record->save();
            }
        }
        else if ($currentOrder < $newOrder)
        {
            $record = AdminOurCollection::where('sorting', '<=', $newOrder)->where('sorting', '>=', $currentOrder)->whereNotNull('sorting')->orderBy('sorting', 'asc')->get();
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
            return view("admin.page.our-collection.add", compact('languageList', 'isActive', 'sortingNumber'));
        }
        else
        {
            return redirect()->route('admin.page.our-collection.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    /*
     * Insert
     */

    public function store(Request $request)
    {
        $languageList      = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule    = [];
        $validationMessage = [];

        $sortingNumber = $this->getSortingNumber();

        $width  = config('custom.collection_image_dimensions_width');
        $height = config('custom.collection_image_dimensions_height');

        $validationRule = [
            'title'       => 'required',
            'description' => 'required',
            'link'        => 'required|url',
            'sorting'     => "required|numeric|gte:1|max:$sortingNumber",
            'is_active'   => 'required',
            'photo'       => 'required|image|mimes:jpg,jpeg,png|dimensions:width=' . $width . ',height=' . $height,
        ];

        $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
        $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

        $validationMessage = [
            'title.required'       => Lang::get($this->langFile . '.error-slider-req-title'),
            'description.required' => Lang::get($this->langFile . '.error-slider-req-description'),
            'link.required'        => Lang::get($this->langFile . '.error-slider-req-link'),
            'link.url'             => Lang::get($this->langFile . '.error-url-link'),
            'sorting.required'     => Lang::get($this->langFile . '.error-req-sorting'),
            'sorting.numeric'      => Lang::get($this->langFile . '.error-numeric-sorting'),
            'sorting.gte'          => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.min'          => Lang::get($this->langFile . '.error-min-sorting'),
            'sorting.max'          => $message_text_sorting,
            'is_active.required'   => Lang::get($this->langFile . '.error-slider-req-status'),
            'photo.required'       => Lang::get($this->langFile . '.error-slider-req-photo'),
            'photo.image'          => Lang::get($this->langFile . '.error-slider-image-photo'),
            'photo.mimes'          => Lang::get($this->langFile . '.error-slider-mimes-photo'),
            'photo.dimensions'     => Lang::get($this->langFile . '.error-collection-dimensions-photo'),
        ];

        $validatedData = $request->validate($validationRule, $validationMessage);

        DB::beginTransaction();

        try
        {
            /*
             * Insert status Flag
             */
            $flag  = true;
            $model = new AdminOurCollection();
            /*
             *  Image
             */
            if ($request->file('photo')) {
					// Get the uploaded image
					$image = $request->file('photo');

					// Generate a WebP file name
					$fileName = uniqid() . '.webp';

					// Define the storage path
					$filePath = config('filepath.collection_image') . '/' . $fileName;

					// Convert the image to WebP
					$webpImage = Image::make($image)->encode('webp', 90); // 90 is quality (0-100)

					// Store WebP image in storage
					Storage::disk('local')->put($filePath, $webpImage->stream());

					// Delete the old image if it exists
					if (!empty($model->photo)) {
						Storage::disk('local')->delete($model->photo);
					}

					// Save the new WebP image path in the database
					$model->photo = $filePath;
			}
            /*
             * code for sorting
             */
            $model->sorting     = $request->sorting;
            $this->updateSortingOrder($sortingNumber, $request->sorting);
            /*
             * end of code for sorting
             */
            $model->title       = $request->title;
            $model->description = $request->description;
            $model->link        = $request->link;
            $model->is_active   = $request->is_active;
            /*
             * Save Main Record
             */
            if ($model->save())
            {
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
        return redirect()->route('admin.page.our-collection.index')->with($status, $message_text);
    }

    /*
     * Edit
     */

    public function edit($id)
    {
        $singleRecord = AdminOurCollection::find($id);
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
                return view("admin.page.our-collection.edit", compact('id', 'isActive', 'languageList', 'singleRecord', 'sortingNumber'));
            }
            else
            {
                return redirect()->route('admin.page.our-collection.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
            }
        }
        return redirect()->route('admin.page.our-collection.index')->with('error', Lang::get($this->langFile . '.record_not_found'));
    }

    /*
     * Update
     */

    public function update(Request $request, $id)
    {
        $model = AdminOurCollection::find($id);
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
            $validationRule    = [];
            $validationMessage = [];

            $width  = config('custom.collection_image_dimensions_width');
            $height = config('custom.collection_image_dimensions_height');

            $validationRule       = [
                'title'       => 'required',
                'description' => 'required',
                'link'        => 'required|url',
                'sorting'     => "required|numeric|gte:1|max:$sortingNumber",
                'is_active'   => 'required',
                'photo'       => 'image|mimes:jpg,jpeg,png|dimensions:width=' . $width . ',height=' . $height,
            ];
            $message_text_sorting = Lang::get($this->langFile . '.error-max-sorting');
            $message_text_sorting = str_replace("#number#", $sortingNumber, $message_text_sorting);

            $validationMessage = [
                'title.required'       => Lang::get($this->langFile . '.error-slider-req-title'),
                'description.required' => Lang::get($this->langFile . '.error-slider-req-description'),
                'link.required'        => Lang::get($this->langFile . '.error-slider-req-link'),
                'link.url'             => Lang::get($this->langFile . '.error-url-link'),
                'sorting.required'     => Lang::get($this->langFile . '.error-req-sorting'),
                'sorting.numeric'      => Lang::get($this->langFile . '.error-numeric-sorting'),
                'sorting.gte'          => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.min'          => Lang::get($this->langFile . '.error-min-sorting'),
                'sorting.max'          => $message_text_sorting,
                'is_active.required'   => Lang::get($this->langFile . '.error-slider-req-status'),
                'photo.image'          => Lang::get($this->langFile . '.error-slider-image-photo'),
                'photo.mimes'          => Lang::get($this->langFile . '.error-slider-mimes-photo'),
                'photo.dimensions'     => Lang::get($this->langFile . '.error-collection-dimensions-photo'),
            ];

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
					$filePath = config('filepath.collection_image') . '/' . $fileName;

					// Convert the image to WebP
					$webpImage = Image::make($image)->encode('webp', 90); // 90 is quality (0-100)

					// Store WebP image in storage
					Storage::disk('local')->put($filePath, $webpImage->stream());

					// Delete the old image if it exists
					if (!empty($model->photo)) {
						Storage::disk('local')->delete($model->photo);
					}

					// Save the new WebP image path in the database
					$model->photo = $filePath;
			}
                $model->title       = $request->title;
                $model->description = $request->description;
                $model->link        = $request->link;
                /* sorting during update */
                $model->sorting     = $request->sorting;
                $this->updateSortingOrder($currentOrder, $request->sorting);

                $model->is_active = $request->is_active;
                /*
                 * Save Main Record
                 */
                if ($model->save())
                {
                    if ($flag = true)
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
        return redirect()->route('admin.page.our-collection.index')->with($status, $message_text);
    }

    /*
     * Delete
     */

    public function delete($id)
    {
        $bannerCount = AdminOurCollection::all()->count();
        if ($bannerCount > 1)
        {
            if ($id)
            {
                $record = AdminOurCollection::find($id);

                if ($record)
                {
                    DB::beginTransaction();

                    try
                    {
                        /*
                         * While Delete Change Sorting
                         */
                        $sortingNumber = $this->getSortingNumber();
                        $sortingNumber = $sortingNumber - 1;
                        $currentOrder  = $record->sorting;
                        $currentOrder  = $currentOrder ? $currentOrder : $sortingNumber;
                        $this->updateSortingOrder($currentOrder, $sortingNumber);

                        if ($record->delete())
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
        return redirect()->route('admin.page.our-collection.index')->with($status, $message_text);
    }

    /*
     * Change Status
     */

    public function changeStatus($id, $active)
    {
        $singleRecord = AdminOurCollection::find($id);
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

            return redirect()->route('admin.page.our-collection.index')->with($status, $message_text);
        }
    }

}
