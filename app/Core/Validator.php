<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function validate(array $data, array $rules, array $labels = []): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $label = $labels[$field] ?? $field;
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                [$name, $parameter] = array_pad(explode(':', $rule, 2), 2, null);

                if ($name === 'required' && self::isEmpty($value)) {
                    $errors[$field] = "Поле \"{$label}\" обязательно.";
                    break;
                }

                if (self::isEmpty($value)) {
                    continue;
                }

                if ($name === 'numeric' && !is_numeric($value)) {
                    $errors[$field] = "Поле \"{$label}\" должно быть числом.";
                    break;
                }

                if ($name === 'integer' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $errors[$field] = "Поле \"{$label}\" должно быть целым числом.";
                    break;
                }

                if ($name === 'min' && (float) $value < (float) $parameter) {
                    $errors[$field] = "Поле \"{$label}\" должно быть не меньше {$parameter}.";
                    break;
                }

                if ($name === 'max' && mb_strlen((string) $value) > (int) $parameter) {
                    $errors[$field] = "Поле \"{$label}\" должно быть не длиннее {$parameter} символов.";
                    break;
                }

                if ($name === 'in') {
                    $allowed = explode(',', (string) $parameter);
                    if (!in_array((string) $value, $allowed, true)) {
                        $errors[$field] = "Поле \"{$label}\" содержит недопустимое значение.";
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    private static function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }
}
