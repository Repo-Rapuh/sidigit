<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class Permission extends Model
{
    use HasFactory, LogsAllActivity, SoftDeletes, BlameableTrait;

    protected $fillable = ['name', 'slug', 'menu_id'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role')->withTimestamps();;
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
