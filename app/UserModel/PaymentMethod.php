<?php

namespace App\UserModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\UserModel\PaymentMethodDescription;

class PaymentMethod extends Model
{

    use SoftDeletes;

    protected $table   = 'payment_method';
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

    public function getAllData($searchText = '', $sortKey = '', $sortVal = '', $pageLength)
    {
        switch ($sortKey)
        {
            /*
             * if sortkey = title
             */
            case 'payment_method_name':
                $record = $this->sortByDescription($sortKey, $sortVal);
                $record = implode(",", $record);
                $query  = $this->orderByRaw("FIELD(id, $record)");
                break;
            default:
                $query  = $this->orderBy($sortKey, $sortVal);
        }

        if ($searchText)
        {
            $query->where(function ($query) use ($searchText)
            {
                $query->orWhere('sorting', 'LIKE', '%' . $searchText . '%');
                $record = $this->searchByDescription('question', $searchText);
                if ($record)
                {
                    $query->orWhereIn('id', $record);
                }
                $record = $this->searchByDescription('answer', $searchText);
                if ($record)
                {
                    $query->orWhereIn('id', $record);
                }
                /*
                 * Search by Active/Inactive Keyword
                 */
                if (strtolower($searchText) == 'active')
                {
                    $query->orWhere('is_active', 1);
                }
                if (strtolower($searchText) == 'inactive')
                {
                    $query->orWhere('is_active', 0);
                }
            });
        }
        if ($pageLength)
        {
            $paginate = strtolower($pageLength) == 'all' ? $query->count() : $pageLength;
        }
        else
        {
            $paginate = config('custom.per_page', 10);
        }
        return $query->paginate($paginate);
    }

    public static function getPaymentMethod()
    {
        return PaymentMethod::where('is_active', 1)->orderBy('sorting', 'asc')->get();
    }

    public function getDescription($languageId = "1", $getAll = false)
    {
        $query = $this->hasMany(PaymentMethodDescription::class, 'payment_method_id', 'id');
        if ($languageId)
        {
            $query->where('language_id', $languageId);
        }
        return $getAll ? $query->get() : $query->first();
    }

    /*
     * Sorting Functions
     */

    public function sortByDescription($sortKey, $sortVal)
    {
        return PaymentMethodDescription::orderBy($sortKey, $sortVal)->pluck('payment_method_id')->toArray();
    }

    /*
     * search By Title
     */

    public function searchByDescription($column, $searchText)
    {
        return PaymentMethodDescription::where($column, 'LIKE', '%' . $searchText . '%')->pluck('faq_id');
    }

}
