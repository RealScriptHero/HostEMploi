<?php

namespace App\Helpers;

/**
 * Safe Blade Template Helpers
 * 
 * Provides null-safe access to model relationships and properties
 */
class BladeHelper
{
    /**
     * Safely get nested properties with fallback
     * 
     * @param  mixed   $object
     * @param  string  $path  (e.g., "formateur.nom")
     * @param  mixed   $default
     * @return mixed
     */
    public static function get($object, $path, $default = 'N/A')
    {
        if (!$object) {
            return $default;
        }

        $parts = explode('.', $path);
        $current = $object;

        foreach ($parts as $part) {
            if (is_array($current)) {
                $current = $current[$part] ?? null;
            } elseif (is_object($current)) {
                $current = $current->{$part} ?? null;
            } else {
                return $default;
            }

            if ($current === null) {
                return $default;
            }
        }

        return $current;
    }

    /**
     * Format a name safely with fallback
     * 
     * @param  object|array  $model
     * @param  string        $firstField
     * @param  string        $lastField
     * @return string
     */
    public static function formatName($model, $firstField = 'prenom', $lastField = 'nom')
    {
        if (!$model) {
            return 'Unknown';
        }

        $first = '';
        $last = '';

        if (is_array($model)) {
            $first = $model[$firstField] ?? '';
            $last = $model[$lastField] ?? '';
        } elseif (is_object($model)) {
            $first = $model->{$firstField} ?? '';
            $last = $model->{$lastField} ?? '';
        }

        $name = trim("$first $last");
        return !empty($name) ? $name : 'Unknown';
    }

    /**
     * Get array value safely
     * 
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function arr($array, $key, $default = 'N/A')
    {
        if (!is_array($array)) {
            return $default;
        }

        return $array[$key] ?? $default;
    }

    /**
     * Check if relationship exists and is not empty
     * 
     * @param  object  $model
     * @param  string  $relation
     * @return bool
     */
    public static function hasRelation($model, $relation)
    {
        if (!$model || !is_object($model)) {
            return false;
        }

        try {
            $value = $model->{$relation};
            return $value !== null && (is_object($value) || (is_array($value) && !empty($value)));
        } catch (\Exception $e) {
            return false;
        }
    }
}
