<?php

namespace App\UserModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class TemporaryOrder extends Model
{
    use SoftDeletes;

    protected $table = 'temporary_orders';
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'subtotal' => 'float',
        'shipping_price' => 'float',
        'discount_val' => 'float',
        'discount_amount' => 'float',
        'grand_total' => 'float',
        'advance_price' => 'float',
        'pending_amount' => 'float',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->user()->id;
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->user()->id;
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->user()->id;
                $model->save();
            }
        });
    }

    /**
     * Relationship with details
     */
    public function details()
    {
        return $this->hasMany(TemporaryOrderDetail::class, 'temporary_order_id', 'id')->orderBy('sort_order', 'asc');
    }

    /**
     * Relationship with user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relationship with billing/customer address
     */
    public function billingAddress()
    {
        return $this->belongsTo(\App\UserModel\UserAddress::class, 'customer_address_id', 'id');
    }

    /**
     * Accessor for billing_address_id mapping to customer_address_id
     */
    public function getBillingAddressIdAttribute()
    {
        return $this->customer_address_id;
    }

    /**
     * Mutator for billing_address_id mapping to customer_address_id
     */
    public function setBillingAddressIdAttribute($value)
    {
        $this->attributes['customer_address_id'] = $value;
    }

    /**
     * Relationship with country
     */
    public function country()
    {
        return $this->belongsTo(\App\AdminModel\AdminCountry::class, 'country_id', 'id');
    }

    /**
     * Get all temporary orders for admin listing with search, filtering, and sorting
     */
    public function getAllAdminData($searchText, $sortKey, $sortVal, $pageLength, $filter = [])
    {
        $query = self::with(['user', 'country', 'details']);

        // Sorting
        switch ($sortKey) {
            case 'name':
            case 'customer_name':
                $query->orderBy('customer_name', $sortVal);
                break;
            case 'email':
            case 'customer_email':
                $query->orderBy('customer_email', $sortVal);
                break;
            case 'phone':
            case 'customer_phone':
                $query->orderBy('customer_phone', $sortVal);
                break;
            case 'type':
                $query->orderBy('type', $sortVal);
                break;
            case 'custom_order_number':
                $query->orderBy('custom_order_number', $sortVal);
                break;
            case 'status':
                $query->orderBy('status', $sortVal);
                break;
            case 'total':
            case 'grand_total':
                $query->orderBy('grand_total', $sortVal);
                break;
            case 'advance':
            case 'advance_price':
            case 'paid_amount':
                $query->orderBy('advance_price', $sortVal);
                break;
            case 'pending':
            case 'pending_amount':
                $query->orderBy('pending_amount', $sortVal);
                break;
            case 'created_at':
            case 'order_date':
                $query->orderBy('created_at', $sortVal);
                break;
            default:
                $query->orderBy($sortKey ?: 'id', $sortVal ?: 'desc');
                break;
        }

        // Global search
        if (!empty($searchText)) {
            $query->where(function ($q) use ($searchText) {
                $q->where('id', 'LIKE', '%' . $searchText . '%')
                  ->orWhere('custom_order_number', 'LIKE', '%' . $searchText . '%')
                  ->orWhere('customer_name', 'LIKE', '%' . $searchText . '%')
                  ->orWhere('customer_email', 'LIKE', '%' . $searchText . '%')
                  ->orWhere('customer_phone', 'LIKE', '%' . $searchText . '%')
                  ->orWhere('type', 'LIKE', '%' . $searchText . '%')
                  ->orWhere('status', 'LIKE', '%' . $searchText . '%')
                  ->orWhere('grand_total', 'LIKE', '%' . $searchText . '%')
                  ->orWhere('advance_price', 'LIKE', '%' . $searchText . '%')
                  ->orWhere('pending_amount', 'LIKE', '%' . $searchText . '%')
                  ->orWhereDate('created_at', '=', date('Y-m-d', strtotime($searchText)));
            });
        }

        // Filters
        if (!empty($filter['type'])) {
            $types = is_array($filter['type']) ? $filter['type'] : [$filter['type']];
            $query->whereIn('type', $types);
        }

        if (!empty($filter['customOrderType'])) {
            $customTypes = is_array($filter['customOrderType']) ? $filter['customOrderType'] : [$filter['customOrderType']];
            $query->whereIn('type', $customTypes);
        }

        if (!empty($filter['status'])) {
            $statuses = is_array($filter['status']) ? $filter['status'] : [$filter['status']];
            $query->whereIn('status', $statuses);
        }

        if (!empty($filter['orderStatus'])) {
            $orderStatuses = is_array($filter['orderStatus']) ? $filter['orderStatus'] : [$filter['orderStatus']];
            $query->whereIn('status', $orderStatuses);
        }

        $perPage = ($pageLength && is_numeric($pageLength) && $pageLength > 0) ? (int)$pageLength : (int)config('custom.page_length', 10);

        return $query->paginate($perPage);
    }

    /**
     * Scope for pending temporary orders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Relationship / Reference to linked actual Order
     */
    public function linkedOrder()
    {
        return $this->hasOne(\App\UserModel\Order::class, 'user_id', 'user_id')
            ->where(function ($q) {
                $q->where('custom_order_details', 'LIKE', '%"temporary_order_id":' . $this->id . '%')
                  ->orWhere('custom_order_details', 'LIKE', '%"temporary_order_id":"' . $this->id . '"%');
            });
    }

    /**
     * Get linked actual Order instance
     *
     * @return \App\UserModel\Order|null
     */
    public function getLinkedOrder()
    {
        // 1. Check order_id stored in this TemporaryOrder's custom_order_details
        $customDetails = is_array($this->custom_order_details)
            ? $this->custom_order_details
            : (json_decode($this->custom_order_details, true) ?: []);

        if (!empty($customDetails['order_id'])) {
            $order = \App\UserModel\Order::where('id', $customDetails['order_id'])
                ->where('user_id', $this->user_id)
                ->first();
            if ($order) {
                return $order;
            }
        }

        // 2. Check temporary_order_id stored in Order's custom_order_details
        if (!empty($this->id)) {
            $order = \App\UserModel\Order::where('user_id', $this->user_id)
                ->where(function ($q) {
                    $q->where('custom_order_details', 'LIKE', '%"temporary_order_id":' . $this->id . '%')
                      ->orWhere('custom_order_details', 'LIKE', '%"temporary_order_id":"' . $this->id . '"%')
                      ->orWhere('custom_order_details', 'LIKE', '%"temporary_order_id": ' . $this->id . '%');
                })
                ->orderBy('id', 'desc')
                ->first();

            if ($order) {
                return $order;
            }
        }

        return null;
    }

    /**
     * Map actual order status (ID or string) to temporary order status slug.
     *
     * @param mixed $orderStatus
     * @return string
     */
    public static function mapOrderStatusToTempStatus($orderStatus)
    {
        if (is_null($orderStatus) || $orderStatus === '') {
            return 'pending';
        }

        // Direct string check
        if (is_string($orderStatus) && !is_numeric($orderStatus)) {
            $normalized = strtolower(trim($orderStatus));
            switch ($normalized) {
                case 'pending':
                    return 'pending';
                case 'process':
                case 'processing':
                    return 'processing';
                case 'ready to ship':
                case 'ready_to_ship':
                    return 'ready_to_ship';
                case 'shipped':
                    return 'shipped';
                case 'delivered':
                case 'completed':
                case 'complete':
                    return 'completed';
                case 'cancel':
                case 'cancelled':
                case 'canceled':
                    return 'cancelled';
                case 'return':
                case 'returned':
                    return 'returned';
                default:
                    return $normalized;
            }
        }

        // Numeric ID lookup
        $statusId = (int) $orderStatus;
        switch ($statusId) {
            case 1: // Process / Processing
                return 'processing';
            case 2: // Ready to Ship
                return 'ready_to_ship';
            case 3: // Shipped
                return 'shipped';
            case 4: // Delivered / Completed
                return 'completed';
            case 5: // Cancel / Cancelled
                return 'cancelled';
            case 6: // Return / Returned
                return 'returned';
            case 7: // Pending
                return 'pending';
            default:
                try {
                    $statusRecord = \App\AdminModel\Order\AdminOrderStatus::find($statusId);
                    if ($statusRecord && !empty($statusRecord->order_status)) {
                        return self::mapOrderStatusToTempStatus($statusRecord->order_status);
                    }
                } catch (\Exception $e) {
                    // Fallback
                }
                return 'pending';
        }
    }

    /**
     * Synchronize Temporary Order status when actual Order status changes.
     *
     * @param \Illuminate\Database\Eloquent\Model $order Order or WebOrder instance
     * @return bool
     */
    public static function syncStatusFromOrder($order)
    {
        if (!$order) {
            return false;
        }

        $tempOrderId = null;

        // Check if custom_order_details contains temporary_order_id
        if (!empty($order->custom_order_details)) {
            $details = is_array($order->custom_order_details)
                ? $order->custom_order_details
                : json_decode($order->custom_order_details, true);

            if (isset($details['temporary_order_id']) && !empty($details['temporary_order_id'])) {
                $tempOrderId = $details['temporary_order_id'];
            }
        }

        $tempOrder = null;

        if ($tempOrderId) {
            // Enforce user isolation and validate existence
            $tempOrder = self::where('id', $tempOrderId)
                ->where('user_id', $order->user_id)
                ->first();
        }

        // Fallback: search by order_id reference inside TemporaryOrder custom_order_details if not found by temporary_order_id
        if (!$tempOrder && !empty($order->id)) {
            $tempOrder = self::where('user_id', $order->user_id)
                ->where(function ($q) use ($order) {
                    $q->where('custom_order_details', 'LIKE', '%"order_id":' . $order->id . '%')
                      ->orWhere('custom_order_details', 'LIKE', '%"order_id":"' . $order->id . '"%');
                })
                ->first();
        }

        if ($tempOrder) {
            $targetStatus = self::mapOrderStatusToTempStatus($order->order_status);
            if ($tempOrder->status !== $targetStatus) {
                $tempOrder->status = $targetStatus;
                if (auth()->check()) {
                    $tempOrder->updated_by = auth()->id();
                }
                $tempOrder->save();
                return true;
            }
        }

        // If this is a parent order, also check and sync for any child sub-orders
        if (empty($tempOrder) && !empty($order->id)) {
            $subOrders = \App\UserModel\Order::where('parent_id', $order->id)->get();
            foreach ($subOrders as $subOrder) {
                self::syncStatusFromOrder($subOrder);
            }
        }

        return false;
    }

    /**
     * Accessor for formatted document number (e.g. PO #819, QO #825)
     */
    public function getFormattedDocumentNumberAttribute()
    {
        $prefix = ($this->type === 'quotation') ? 'QO' : 'PO';
        $number = !empty($this->custom_order_number) ? $this->custom_order_number : $this->id;
        return $prefix . ' #' . $number;
    }

    /**
     * Accessor for status badge CSS class
     */
    public function getStatusBadgeClassAttribute()
    {
        $status = strtolower($this->status ?? 'pending');
        switch ($status) {
            case 'completed':
            case 'delivered':
                return 'badge-success';
            case 'processing':
            case 'process':
                return 'badge-primary';
            case 'shipped':
                return 'badge-info';
            case 'cancelled':
            case 'canceled':
            case 'rejected':
                return 'badge-danger';
            case 'returned':
                return 'badge-secondary';
            case 'pending':
            case 'ready_to_ship':
            default:
                return 'badge-warning';
        }
    }

    /**
     * Accessor for type badge CSS class
     */
    public function getTypeBadgeClassAttribute()
    {
        return ($this->type === 'quotation') ? 'badge-warning' : 'badge-primary';
    }
}

