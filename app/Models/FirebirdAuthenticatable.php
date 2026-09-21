<?php

namespace App\Models;

use Illuminate\Database\Query\Expression;
use Illuminate\Foundation\Auth\User as Authenticatable;

class FirebirdAuthenticatable extends Authenticatable
{
    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Convert timestamp columns before saving
        static::saving(function ($model) {
            $model->convertTimestampsForFirebird();
        });
    }

    /**
     * Convert timestamp columns to Firebird-compatible format.
     *
     * @return void
     */
    protected function convertTimestampsForFirebird(): void
    {
        foreach ($this->getAttributes() as $key => $value) {
            if ($value !== null && !($value instanceof Expression) && $this->isTimestampColumn($key)) {
                if ($value instanceof \DateTime) {
                    $this->attributes[$key] = new Expression("CAST('" . $value->format('Y-m-d H:i:s') . "' AS TIMESTAMP)");
                } elseif (is_string($value) && $this->isValidTimestamp($value)) {
                    $this->attributes[$key] = new Expression("CAST('" . $value . "' AS TIMESTAMP)");
                }
            }
        }
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
            'visit_date',
            'email_verified_at',
            // kolom timestamp legacy
            'TANGGAL', 'JAM_CHECKIN', 'TGL_APPROVE', 'DIBUAT',
            'TGL_JATUH_TEMPO', 'TGL_JT', 'TGL_HARGA', 'RNC_TGL_KIRIM',
            'TGL_EXP', 'TGL_PO', 'TGL_AWAL', 'TGL_AKHIR', 'RLS_TGL_KIRIM',
        ]) || str_ends_with($column, '_at') || str_ends_with($column, '_date');
    }

    /**
     * Check if a string is a valid timestamp.
     *
     * @param  string  $value
     * @return bool
     */
    protected function isValidTimestamp(string $value): bool
    {
        return strtotime($value) !== false && preg_match('/^\d{4}-\d{2}-\d{2}/', $value);
    }

    /**
     * Determine if the given attribute is a standard date format.
     *
     * Override to handle Expression objects for Firebird.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function isStandardDateFormat($value)
    {
        if ($value instanceof Expression) {
            return false;
        }

        return parent::isStandardDateFormat($value);
    }

    /**
     * Get the attributes that should be converted to dates.
     *
     * Override to handle Expression objects for Firebird.
     *
     * @return array
     */
    public function getDates()
    {
        $dates = parent::getDates();
        
        // Remove columns that have Expression objects (already converted for Firebird)
        return array_filter($dates, function ($date) {
            return !(($this->attributes[$date] ?? null) instanceof Expression);
        });
    }

    /**
     * Get the primary key value as 'id' attribute for view compatibility.
     *
     * @return mixed
     */
    public function getIdAttribute()
    {
        return $this->getKey();
    }
}

