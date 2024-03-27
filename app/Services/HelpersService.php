<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

class HelpersService
{
  /**
   * Prepare collection for front-end selects
   *
   * @param array|Collection $collection
   * @param $key_field
   * @param $label_field
   * @return array|array[]|bool|Collection|\Illuminate\Support\Collection
   */
  public static function forSelects(Collection|array $collection, $key_field = null, $label_field = null)
  {
    if ($collection instanceof Collection) {
      return $collection->map(function ($item) use ($key_field, $label_field) {
        return [
          'key' => $item->{$key_field},
          'label' => $item->{$label_field}
        ];
      })->pluck('label', 'key');
    } elseif (is_array($collection)) {
      array_walk($collection, function ($k, $v) {
        return [
          'key' => $k,
          'label' => $v
        ];
      });
      return $collection;
    } else {
      return [];
    }
  }

  public static function forMultiselects(Collection|array $collection, $key_field = null, $label_field = null)
  {
    if ($collection instanceof Collection) {
      return $collection->map(function ($item) use ($key_field, $label_field) {
        return [
          'value' => $item->{$key_field},
          'label' => $item->{$label_field}
        ];
      })->toArray();
    } elseif (is_array($collection)) {
      array_walk($collection, function ($k, $v) {
        return [
          'value' => $k,
          'label' => $v
        ];
      });
      return $collection;
    } else {
      return [];
    }
  }

  /**
   * Prepare collection for front-end options
   *
   * @param Collection|array $collection
   * @param $key_field
   * @param $label_field
   * @return array|Collection|\Illuminate\Support\Collection
   */
  public static function forOptions(Collection|array $collection, $key_field = null, $label_field = null)
  {
    if ($collection instanceof Collection) {
      return $collection->map(function ($item) use ($key_field, $label_field) {
        return [
          'value' => $item->{$key_field},
          'label' => $item->{$label_field}
        ];
      })->pluck('label', 'value');
    } elseif (is_array($collection)) {
      return array_map(function ($k, $l) {
        return [
          'value' => $k,
          'label' => $l
        ];
      }, array_keys($collection), array_values($collection));
    } else {
      return [];
    }
  }

  /**
   * Text preview
   *
   * @param $text
   * @param $limit
   * @param $default
   * @return mixed|string
   */
  public static function textPreview($text = null, $limit = 10, $default = '')
  {
    if ($text && $limit) {
      if (strlen($text) > $limit) return substr($text, 0, $limit) . '...';
      return $text;
    }
    return $default;
  }

  /**
   * Parse date string
   *
   * @param $string
   * @param $format
   * @return \Carbon\Carbon|false|null
   */
  public static function parseDateString($string = '', $format = 'd/m/Y')
  {
    $carbon = null;
    if ($string) {
      try {
        $carbon = Carbon::createFromFormat($format, $string);
      } catch (\Exception $ex) {
        Log::error('Helper services (parse date string): ' . $ex->getMessage());
      }
    }
    return $carbon;
  }

  /**
   * Make search query
   *
   * @param $query
   * @param array $fields
   * @return \Carbon\Carbon|false|null
   */
  public static function makeSearchQuery($queryBuilder, $searchQuery, $fields)
  {
    $queryBuilder->where(function ($q) use ($searchQuery, $fields) {
      $explodedQuery = explode(' ', $searchQuery ?? '');
      foreach ($explodedQuery as $singleQuery) {
        $q->where(function ($q) use ($singleQuery, $fields) {
          foreach ($fields as $field) {
            $explodedField = explode('.', $field);
            if (count($explodedField) == 1) $q->orWhere($field, 'like', "%{$singleQuery}%");
            if (count($explodedField) == 2) $q->orWhereRelation($explodedField[0], $explodedField[1], 'like', "%{$singleQuery}%");
          }
        });
      }
    });
  }

    /**
     * Check if request is in allowed routes
     *
     * @param Request $request
     * @param $routes
     * @return bool
     */
    public static function checkAllowRoute(Request $request, $routes = [])
    {
        return in_array($request->route()->getName(), $routes);
    }
}
