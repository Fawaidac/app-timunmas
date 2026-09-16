<?php

namespace App\Traits;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

trait FirebirdTimestamp
{
    /**
     * Get a new query builder for the model's table.
     *
     * @param  string  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newFirebirdQuery($query = null)
    {
        if ($query === null) {
            $query = $this->newQueryWithoutScopes();
        }

        return $query;
    }

    /**
     * Create a new record in the database.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function createFirebird(array $attributes = [])
    {
        $model = new static();
        $attributes = $model->convertTimestampsForFirebird($attributes);
        $model->fill($attributes);
        $model->save();
        return $model;
    }

    /**
     * Convert timestamp attributes for Firebird compatibility.
     *
     * @param  array  $attributes
     * @return array
     */
    protected function convertTimestampsForFirebird(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            if ($value !== null && $this->isTimestampColumn($key)) {
                if ($value instanceof \DateTime) {
                    $attributes[$key] = new Expression("CAST('" . $value->format('Y-m-d H:i:s') . "' AS TIMESTAMP)");
                } elseif (is_string($value) && strtotime($value) !== false) {
                    $attributes[$key] = new Expression("CAST('" . $value . "' AS TIMESTAMP)");
                }
            }
        }
        return $attributes;
    }

    /**
     * Check if a column is a timestamp column.
     *
     * @param  string  $column
     * @return bool
     */
    protected function isTimestampColumn(string $column): bool
    {
        return in_array($column, [
            'created_at',
            'updated_at',
            'deleted_at',
            'checkin_time',
            'approved_at',
            'order_date',
            'invoice_date',
            'due_date',
        ]) || str_ends_with($column, '_at') || str_ends_with($column, '_date');
    }
}
