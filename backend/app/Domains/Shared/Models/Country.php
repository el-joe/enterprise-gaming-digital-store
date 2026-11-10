<?php

namespace App\Domains\Shared\Models;

use App\Domains\Catalog\Models\Region;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Table('countries', key: 'code', keyType: 'string', incrementing: false)]
#[Fillable(['code', 'name', 'dial_code', 'default_currency', 'is_active'])]
#[UseFactory(CountryFactory::class)]
class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Region, $this>
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'region_country', 'country_code', 'region_id');
    }
}
