<?php

namespace App\Models;

use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

class Product extends Model
{
    use HasFactory, LogsAllActivity, BlameableTrait, SoftDeletes;

    protected $table = 'mst_products';

    protected $fillable = [
        'sku',
        'name',
        'base_price',
        'sale_price',
        'length_cm',
        'width_cm',
        'unit_id',
        'category_id',
        'description',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'length_cm' => 'decimal:2',
        'width_cm' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function productMaterials()
    {
        return $this->hasMany(ProductMaterial::class, 'product_id');
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'mst_product_materials', 'product_id', 'material_id')
            ->withPivot(['quantity'])
            ->withTimestamps();
    }
}
