<?php

namespace App\Http\Controllers\Admin\Pages\AboutUs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Helper;
use DB;
use Illuminate\Support\Facades\Storage;
use App\AdminModel\GeneralSetting\AboutUs\AboutUs;
use App\AdminModel\GeneralSetting\AboutUs\AboutUsDescription;

class AboutUsController extends Controller
{

    private $langFile = 'admin';
    private $module = '.about_us';

    /*
     * open insert page
     */

    public function create()
    {
        $languageList = Helper::getLanguageList();
        $aboutUs = AboutUs::all()->first();
        if ($languageList)
        {
            $isActive = config('admin.product.isActive');
            return view("admin.page.about-us.add", compact('languageList', 'isActive', 'aboutUs'));
        }
        else
        {
            return redirect()->route('admin.page.about-us.index')->with('error', Lang::get($this->langFile . 'language-not-found'));
        }
    }

    /*
     * Insert
     */

    public function store(Request $request)
    {

        $languageList = Helper::getLanguageList();
        /*
         * Validations
         */
        $validationRule = [];
        $validationMessage = [];

        $validationRule = [
            'about_us_image_1' => 'image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.about_us_image_dimensions_height') . ',height=' . config('custom.about_us_image1_dimensions_height'),
            'about_us_image_2' => 'image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.about_us_image_dimensions_height') . ',height=' . config('custom.about_us_image_dimensions_height'),
            'about_us_image_3' => 'image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.about_us_image_dimensions_height') . ',height=' . config('custom.about_us_image_dimensions_height'),
            'about_us_image_4' => 'image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.about_us_image_dimensions_height') . ',height=' . config('custom.about_us_image_dimensions_height'),
            'button_link' => 'required|url',
        ];

        $validationMessage = [
            'button_link.required' => Lang::get($this->langFile . '.error-slider-req-link'),
            'button_link.url' => Lang::get($this->langFile . '.error-url-link'),
            'about_us_image_1.required' => Lang::get($this->langFile . '.error-about-1-us-req-photo'),
            'about_us_image_1.image' => Lang::get($this->langFile . '.error-about-1-us-image-photo'),
            'about_us_image_1.mimes' => Lang::get($this->langFile . '.error-about-1-us-mimes-photo'),
            'about_us_image_1.dimensions' => Lang::get($this->langFile . '.about_1_us_image_dimention'),
            'about_us_image_2.required' => Lang::get($this->langFile . '.error-about-2-us-req-photo'),
            'about_us_image_2.image' => Lang::get($this->langFile . '.error-about-2-us-image-photo'),
            'about_us_image_2.mimes' => Lang::get($this->langFile . '.error-about-2-us-mimes-photo'),
            'about_us_image_2.dimensions' => Lang::get($this->langFile . '.about_2_us_image_dimention'),
            'about_us_image_3.required' => Lang::get($this->langFile . '.error-about-3-us-req-photo'),
            'about_us_image_3.image' => Lang::get($this->langFile . '.error-about-3-us-image-photo'),
            'about_us_image_3.mimes' => Lang::get($this->langFile . '.error-about-3-us-mimes-photo'),
            'about_us_image_3.dimensions' => Lang::get($this->langFile . '.about_3_us_image_dimention'),
            'about_us_image_4.image' => Lang::get($this->langFile . '.error-about-4-us-image-photo'),
            'about_us_image_4.mimes' => Lang::get($this->langFile . '.error-about-4-us-mimes-photo'),
            'about_us_image_4.dimensions' => Lang::get($this->langFile . '.about_4_us_image_dimention'),
            'about_us_home_big.required' => Lang::get($this->langFile . '.error-about-big-us-req-photo'),
            'about_us_home_big.image' => Lang::get($this->langFile . '.error-about-big-us-image-photo'),
            'about_us_home_big.mimes' => Lang::get($this->langFile . '.error-about-big-us-mimes-photo'),
            'about_us_home_big.dimensions' => Lang::get($this->langFile . '.about_big_us_image_dimention'),
            'about_us_home_small.required' => Lang::get($this->langFile . '.error-about-small-us-req-photo'),
            'about_us_home_small.image' => Lang::get($this->langFile . '.error-about-small-us-image-photo'),
            'about_us_home_small.mimes' => Lang::get($this->langFile . '.error-about-small-us-mimes-photo'),
            'about_us_home_small.dimensions' => Lang::get($this->langFile . '.about_small_us_image_dimention'),
        ];
        /*
         * language wise validation
         */
        foreach ($languageList as $language)
        {
            $message_text = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-about-us-req-title'));
            $validationRule['about_us_description_' . $language->id] = 'required';
            $validationMessage['about_us_description_' . $language->id . '.required'] = $message_text;
        }
        $validatedData = $request->validate($validationRule, $validationMessage);
        DB::beginTransaction();
        try
        {
            $model = new AboutUs();
            /*
             *  Image
             */
            if ($request->file('about_us_image_1'))
            {
                $path = Storage::disk('local')->put(config('filepath.home_about_us'), $request->about_us_image_1);
                if ($path)
                {
                    Storage::disk('local')->delete($model->image_url_1);
                    $model->image_url_1 = $path;
                }
            }

            if ($request->file('about_us_image_2'))
            {
                $path = Storage::disk('local')->put(config('filepath.home_about_us'), $request->about_us_image_2);
                if ($path)
                {
                    Storage::disk('local')->delete($model->image_url_2);
                    $model->image_url_2 = $path;
                }
            }
            if ($request->file('about_us_image_3'))
            {
                $path = Storage::disk('local')->put(config('filepath.home_about_us'), $request->about_us_image_3);
                if ($path)
                {
                    Storage::disk('local')->delete($model->image_url_3);
                    $model->image_url_3 = $path;
                }
            }
            if ($request->file('about_us_image_4'))
            {
                $path = Storage::disk('local')->put(config('filepath.home_about_us'), $request->about_us_image_4);
                if ($path)
                {
                    Storage::disk('local')->delete($model->image_url_4);
                    $model->image_url_4 = $path;
                }
            }
            $model->button_link = $request->button_link;

            $model->meta_title = $request->meta_title;
            $model->meta_description = $request->meta_description;
            $model->meta_keyword = $request->meta_keyword;

            /*
             * Save Main Record
             */
            if ($model->save())
            {
                $flag = true;
                foreach ($languageList as $language)
                {
                    $subModel = new AboutUsDescription();

                    $description = 'about_us_description_' . $language->id;
                    $subModel->admin_about_us_id = $model->id;
                    $subModel->language_id = $language->id;
                    $subModel->note = $request->$description;


                    if (!$subModel->save())
                    {
                        $flag = false;
                    }
                }
                if ($flag)
                {
                    DB::commit();
                    $status = 'success';
                    $message_text = Lang::get($this->langFile . '.add-success');
                    $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                }
                else
                {
                    DB::rollback();
                    $status = 'error';
                    $message_text = Lang::get($this->langFile . '.add-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            }
            else
            {
                DB::rollback();
                $status = 'error';
                $message_text = Lang::get($this->langFile . '.add-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        } catch (Throwable $e)
        {
            DB::rollback();
            $status = 'error';
            $message_text = Lang::get($this->langFile . '.add-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }

        return redirect()->route('admin.page.about-us.add')->with($status, $message_text);
    }

    /*
     * Update
     */

    public function update(Request $request)
    {
        $id = $request->ID;
        $model = AboutUs::find($id);
        $languageList = Helper::getLanguageList();
        if ($model)
        {
            /*
             * Validations
             */
            $validationRule = [];
            $validationMessage = [];
            $validationRule = [
                'button_link' => 'required|url',
                'about_us_image_1' => 'image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.about_us_image_dimensions_width') . ',height=' . config('custom.about_us_image_dimensions_height'),
                'about_us_image_2' => 'image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.about_us_image_dimensions_width') . ',height=' . config('custom.about_us_image_dimensions_height'),
                'about_us_image_3' => 'image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.about_us_image_dimensions_width') . ',height=' . config('custom.about_us_image_dimensions_height'),
                'about_us_image_4' => 'image|mimes:jpg,jpeg,png|dimensions:width=' . config('custom.about_us_image_dimensions_width') . ',height=' . config('custom.about_us_image_dimensions_height'),
            ];

            $validationMessage = [
                'button_link.required' => Lang::get($this->langFile . '.error-slider-req-link'),
                'button_link.url' => Lang::get($this->langFile . '.error-url-link'),
                'about_us_image_1.required' => Lang::get($this->langFile . '.error-about-1-us-req-photo'),
                'about_us_image_1.image' => Lang::get($this->langFile . '.error-about-1-us-image-photo'),
                'about_us_image_1.mimes' => Lang::get($this->langFile . '.error-about-1-us-mimes-photo'),
                'about_us_image_1.dimensions' => Lang::get($this->langFile . '.about_1_us_image_dimention'),
                'about_us_image_2.required' => Lang::get($this->langFile . '.error-about-2-us-req-photo'),
                'about_us_image_2.image' => Lang::get($this->langFile . '.error-about-2-us-image-photo'),
                'about_us_image_2.mimes' => Lang::get($this->langFile . '.error-about-2-us-mimes-photo'),
                'about_us_image_2.dimensions' => Lang::get($this->langFile . '.about_2_us_image_dimention'),
                'about_us_image_3.required' => Lang::get($this->langFile . '.error-about-3-us-req-photo'),
                'about_us_image_3.image' => Lang::get($this->langFile . '.error-about-3-us-image-photo'),
                'about_us_image_3.mimes' => Lang::get($this->langFile . '.error-about-3-us-mimes-photo'),
                'about_us_image_3.dimensions' => Lang::get($this->langFile . '.about_3_us_image_dimention'),
                'about_us_image_4.image' => Lang::get($this->langFile . '.error-about-4-us-image-photo'),
                'about_us_image_4.mimes' => Lang::get($this->langFile . '.error-about-4-us-mimes-photo'),
                'about_us_image_4.dimensions' => Lang::get($this->langFile . '.about_4_us_image_dimention'),
                'about_us_home_big.required' => Lang::get($this->langFile . '.error-about-big-us-req-photo'),
                'about_us_home_big.image' => Lang::get($this->langFile . '.error-about-big-us-image-photo'),
                'about_us_home_big.mimes' => Lang::get($this->langFile . '.error-about-big-us-mimes-photo'),
                'about_us_home_big.dimensions' => Lang::get($this->langFile . '.about_big_us_image_dimention'),
                'about_us_home_small.required' => Lang::get($this->langFile . '.error-about-small-us-req-photo'),
                'about_us_home_small.image' => Lang::get($this->langFile . '.error-about-small-us-image-photo'),
                'about_us_home_small.mimes' => Lang::get($this->langFile . '.error-about-small-us-mimes-photo'),
                'about_us_home_small.dimensions' => Lang::get($this->langFile . '.about_small_us_image_dimention'),
            ];
            /*
             * language wise validation
             */
            foreach ($languageList as $language)
            {
                $message_text = str_replace("#language#", '(' . ucfirst($language->name) . ')', Lang::get($this->langFile . '.error-about-us-req-title'));
                $validationRule['about_us_description_' . $language->id] = 'required';
                $validationMessage['about_us_description_' . $language->id . '.required'] = $message_text;
            }
            $validatedData = $request->validate($validationRule, $validationMessage);
            DB::beginTransaction();
            try
            {
                /*
                 *  Image
                 */
                if ($request->file('about_us_image_1'))
                {
                    $path = Storage::disk('local')->put(config('filepath.home_about_us'), $request->about_us_image_1);
                    if ($path)
                    {
                        Storage::disk('local')->delete($model->image_url_1);
                        $model->image_url_1 = $path;
                    }
                }

                if ($request->file('about_us_image_2'))
                {
                    $path = Storage::disk('local')->put(config('filepath.home_about_us'), $request->about_us_image_2);
                    if ($path)
                    {
                        Storage::disk('local')->delete($model->image_url_2);
                        $model->image_url_2 = $path;
                    }
                }
                if ($request->file('about_us_image_3'))
                {
                    $path = Storage::disk('local')->put(config('filepath.home_about_us'), $request->about_us_image_3);
                    if ($path)
                    {
                        Storage::disk('local')->delete($model->image_url_3);
                        $model->image_url_3 = $path;
                    }
                }
                if ($request->file('about_us_image_4'))
                {
                    $path = Storage::disk('local')->put(config('filepath.home_about_us'), $request->about_us_image_4);
                    if ($path)
                    {
                        Storage::disk('local')->delete($model->image_url_4);
                        $model->image_url_4 = $path;
                    }
                }
                $model->meta_title = $request->meta_title;
                $model->meta_description = $request->meta_description;
                $model->meta_keyword = $request->meta_keyword;


                $model->button_link = $request->button_link;

                /*
                 * Save Main Record
                 */
                if ($model->save())
                {
                    $flag = true;
                    foreach ($languageList as $language)
                    {
                        $subModel = AboutUsDescription::getDataByLanguageID($id, $language->id);

                        $description = 'about_us_description_' . $language->id;
                        $subModel->admin_about_us_id = $model->id;
                        $subModel->language_id = $language->id;
                        $subModel->note = $request->$description;
                        if (!$subModel->save())
                        {
                            $flag = false;
                        }
                    }
                    if ($flag)
                    {
                        DB::commit();
                        $status = 'success';
                        $message_text = Lang::get($this->langFile . '.update-success');
                        $message_text = str_replace("#module#", Lang::get($this->langFile . $this->module), $message_text);
                    }
                    else
                    {
                        DB::rollback();
                        $status = 'error';
                        $message_text = Lang::get($this->langFile . '.update-error');
                        $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                    }
                }
                else
                {
                    DB::rollback();
                    $status = 'error';
                    $message_text = Lang::get($this->langFile . '.update-error');
                    $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
                }
            } catch (Throwable $e)
            {
                DB::rollback();
                $status = 'error';
                $message_text = Lang::get($this->langFile . '.update-error');
                $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
            }
        }
        else
        {
            DB::rollback();
            $status = 'error';
            $message_text = Lang::get($this->langFile . '.update-error');
            $message_text = str_replace("#module#", strtolower(Lang::get($this->langFile . $this->module)), $message_text);
        }
        return redirect()->route('admin.page.about-us.add')->with($status, $message_text);
    }

}
