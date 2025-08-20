<?php
// app/Traits/Auditable.php
namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
        $nameAttributes = ['name', 'title', 'room_name', 'display_name'];
        foreach ($nameAttributes as $attribute) {
            if (isset($this->{$attribute})) {
                return (string) $this->{$attribute};
            }
        }
        return get_class($this) . ' #' . $this->getKey();
    }

    protected static function audit($event, $model)
    {
        
        $userId = Auth::guard('api')->id();

        
        // 3. Nếu có userId, tiếp tục ghi log như bình thường.
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