<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'price', 'description'])]
class Addon extends Model
{
    /** @return HasMany<TenantAddon, $this> */
    public function tenantAddons(): HasMany
    {
        return $this->hasMany(TenantAddon::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }
}
