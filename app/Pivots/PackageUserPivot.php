<?php

namespace App\Pivots;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PackageUserPivot extends Pivot
{
  protected $casts = [
    'services' => AsCollection::class,
  ];
}
