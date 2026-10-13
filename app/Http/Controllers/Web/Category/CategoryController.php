<?php

namespace App\Http\Controllers\Web\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

use App\AdminModel\Product\AdminCategory;
use Helper;

class CategoryController extends Controller
{

    private $module   = '.category';
    private $langFile = 'web';

    public function index(Request $request, $slug = '')
    {
        $category= AdminCategory::where('categories_slug',$slug)->first();
        
        
        $subCategories = $category->getChildFront2($category->id, false, 'product');

       
         
      return view('web.category.index', compact('subCategories','category'));

    }
    
}