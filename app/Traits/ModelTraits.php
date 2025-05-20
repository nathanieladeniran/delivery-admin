<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait ModelTraits
{
    protected static function bootModelTraits()
    {
        static::creating(function ($model) {
            // Check if the primary key is not set
            if (empty($model->{$model->getKeyName()})) {
                // Generate and set a UUID as the primary key
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Initialize the model's traits and properties.
     *
     * @return void
     */
    public function initializeModelTraits()
    {
        $this->guarded = ['id', 'uuid'];
        $this->setIncrementing(false);
        $this->setKeyType('string');
    }

    /**
     * Set whether the model ID is auto-incrementing.
     *
     * @param  bool  $value
     * @return void
     */
    public function setIncrementing($value)
    {
        $this->incrementing = $value;
    }

    /**
     * Set the type of the primary key.
     *
     * @param  string  $value
     * @return void
     */
    public function setKeyType($value)
    {
        $this->keyType = $value;
    }
}
