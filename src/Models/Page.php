<?php namespace WebEd\Base\Pages\Models;

use WebEd\Base\Models\EloquentBase as BaseModel;
use WebEd\Base\Pages\Models\Contracts\PageModelContract;
use WebEd\Base\Users\Models\User;

class Page extends BaseModel implements PageModelContract
{
    protected $table = 'pages';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title', 'page_template', 'slug', 'description', 'content', 'thumbnail', 'keywords', 'status', 'order',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modifier()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @param $value
     * @return string
     */
    public function getContentAttribute($value)
    {
        if (!is_admin_panel()) {
            return do_shortcode($value);
        }
        return $value;
    }
}
