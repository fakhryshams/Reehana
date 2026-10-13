<?php

namespace App\AdminModel\Size;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\AdminModel\Product\AdminCategory;
use App\AdminModel\Product\AdminCategoryDescription;

class AdminSizeCategory extends Model
{

    use SoftDeletes;

    protected $table   = 'admin_size_category';
    protected $guarded = ['created_at,updated_at,deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($data)
        {
            $data->created_by = auth()->user()->id;
        });

        static::updating(function ($data)
        {
            $data->updated_by = auth()->user()->id;
        });

        static::deleting(function ($data)
        {
            $data->deleted_by = auth()->user()->id;
            $data->save();
        });
    }

    public function getCategoryDetails()
    {
        $query = $this->hasOne(AdminCategory::class, 'id', 'category_id')->first();
        if ($query)
        {
            $result = AdminCategoryDescription::where(['categories_id' => $query->id, 'language_id' => 1])->first();
            return $result ? $result : false;
        }
        return false;
    }

}
