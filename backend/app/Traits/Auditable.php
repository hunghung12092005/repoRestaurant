<?php
// app/Traits/Auditable.php
namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
// Bỏ Log::info đi cho sạch sẽ
// use Illuminate\Support\Facades\Log;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            self::audit('created', $model);
        });

        static::updated(function (Model $model) {
            if ($model->wasChanged()) {
                self::audit('updated', $model);
            }
        });

        static::deleted(function (Model $model) {
            self::audit('deleted', $model);
        });
    }

    public function getAuditableName(): string
    {
        $nameAttributes = ['name','code', 'employee_code','type_name','service_name', 'amenity_name','booking_id', 'title', 'room_name', 'display_name'];
        foreach ($nameAttributes as $attribute) {
            if (isset($this->{$attribute})) {
                return (string) $this->{$attribute};
            }
        }
        return get_class($this) . ' #' . $this->getKey();
    }

    protected static function audit($event, $model)
    {
        $userId = null;
        $user = null;

        // <<< BẮT ĐẦU PHẦN SỬA ĐỔI QUAN TRỌNG >>>
        try {
            // 1. Cố gắng xác thực người dùng từ token trong request
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                // Nếu không xác thực được user, không làm gì cả
                return;
            }

            // 2. Kiểm tra vai trò của người dùng
            // Dựa theo logic trong App.vue (isStaff.value = (roleName && roleName !== 'client');)
            // Nếu người dùng không có vai trò, hoặc có vai trò là 'client', thì không ghi log.
            if (!$user->role || $user->role->name === 'client') {
                return; // Dừng lại, không ghi log cho khách hàng
            }
            
            // Nếu vượt qua các kiểm tra trên, đây chắc chắn là Admin/Staff
            $userId = $user->id;

        } catch (JWTException $e) {
            // 3. Nếu có bất kỳ lỗi nào về JWT (không có token, token hết hạn,...)
            // thì đây không phải là một hành động được xác thực của admin/staff.
            return; // Dừng lại, không ghi log
        }
        // <<< KẾT THÚC PHẦN SỬA ĐỔI QUAN TRỌNG >>>

        // Chỉ khi nào $userId được gán (tức là người dùng là Admin/Staff),
        // thì mới tiến hành tạo AuditLog.
        AuditLog::create([
            'user_id'        => $userId,
            'event'          => $event,
            'auditable_id'   => $model->getKey(),
            'auditable_type' => get_class($model),
            'auditable_name' => $model->getAuditableName(),
            'old_values'     => ($event === 'updated' || $event === 'deleted') ? $model->getOriginal() : null,
            'new_values'     => $event !== 'deleted' ? $model->getAttributes() : null,
            'url'            => request()->fullUrl(),
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}