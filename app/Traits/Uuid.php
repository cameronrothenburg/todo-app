<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Uuid {

    /**
     *
     * @return void
     */
    protected static function boot(): void {
        parent::boot();

        static::creating(function ($model) {
           try {
               $model->id = (String) Str::uuid();

           } catch (UnsatisfiedDependencyException  $e) {
               abort(500, $e->getMessage());
           }
        });
    }
}
