<?php

namespace Utils;

class Validator
{
    public static function validateRequired($data, $fields)
    {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[] = "$field is required.";
            }
        }
        return $errors;
    }

    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
            return $data;
        }
        return htmlspecialchars(strip_tags(trim($data)));
    }
}
