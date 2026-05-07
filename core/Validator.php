<?php

class Validator {
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $validated = [];

    public function __construct(array $data, array $rules) {
        $this->data  = $data;
        $this->rules = $rules;
        $this->validate();
    }

    private function validate(): void {
        foreach ($this->rules as $field => $ruleString) {
            $rules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            $required = in_array('required', $rules);
            if (!$required && ($value === null || $value === '')) {
                if ($value !== null) $this->validated[$field] = $value;
                continue;
            }

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
                if (isset($this->errors[$field])) break;
            }

            if (!isset($this->errors[$field])) {
                $this->validated[$field] = $value;
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule): void {
        $label = ucfirst(str_replace('_', ' ', $field));

        if (str_contains($rule, ':')) {
            [$ruleName, $ruleParam] = explode(':', $rule, 2);
        } else {
            $ruleName  = $rule;
            $ruleParam = null;
        }

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->errors[$field] = "$label is required";
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "$label must be a valid email address";
                }
                break;

            case 'min':
                if (is_string($value) && mb_strlen($value) < (int)$ruleParam) {
                    $this->errors[$field] = "$label must be at least $ruleParam characters";
                } elseif (is_numeric($value) && $value < (float)$ruleParam) {
                    $this->errors[$field] = "$label must be at least $ruleParam";
                }
                break;

            case 'max':
                if (is_string($value) && mb_strlen($value) > (int)$ruleParam) {
                    $this->errors[$field] = "$label must not exceed $ruleParam characters";
                } elseif (is_numeric($value) && $value > (float)$ruleParam) {
                    $this->errors[$field] = "$label must not exceed $ruleParam";
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->errors[$field] = "$label must be a number";
                }
                break;

            case 'integer':
                if ($value !== null && $value !== '' && !ctype_digit((string)$value)) {
                    $this->errors[$field] = "$label must be an integer";
                }
                break;

            case 'string':
                if ($value !== null && !is_string($value)) {
                    $this->errors[$field] = "$label must be a string";
                }
                break;

            case 'boolean':
                if ($value !== null && !in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true)) {
                    $this->errors[$field] = "$label must be true or false";
                }
                break;

            case 'in':
                $allowed = explode(',', $ruleParam);
                if ($value !== null && !in_array($value, $allowed)) {
                    $this->errors[$field] = "$label must be one of: $ruleParam";
                }
                break;

            case 'not_in':
                $disallowed = explode(',', $ruleParam);
                if ($value !== null && in_array($value, $disallowed)) {
                    $this->errors[$field] = "$label contains an invalid value";
                }
                break;

            case 'url':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field] = "$label must be a valid URL";
                }
                break;

            case 'alpha_dash':
                if ($value && !preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
                    $this->errors[$field] = "$label may only contain letters, numbers, dashes and underscores";
                }
                break;

            case 'unique':
                $parts  = explode(',', (string)$ruleParam, 2);
                $table  = trim($parts[0]);
                $column = trim($parts[1] ?? '') ?: $field;
                $exceptId = isset($this->data['id']) ? (int)$this->data['id'] : null;
                $sql = "SELECT COUNT(*) as cnt FROM `$table` WHERE `$column` = ?";
                $params = [$value];
                if ($exceptId) {
                    $sql .= " AND id != ?";
                    $params[] = $exceptId;
                }
                $cnt = (int)(Database::fetch($sql, $params)['cnt'] ?? 0);
                if ($cnt > 0) {
                    $this->errors[$field] = "$label is already taken";
                }
                break;

            case 'exists':
                $parts  = explode(',', (string)$ruleParam, 2);
                $table  = trim($parts[0]);
                $column = trim($parts[1] ?? '') ?: $field;
                $cnt = (int)(Database::fetch("SELECT COUNT(*) as cnt FROM `$table` WHERE `$column` = ?", [$value])['cnt'] ?? 0);
                if ($cnt === 0) {
                    $this->errors[$field] = "$label does not exist";
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->errors[$field] = "$label confirmation does not match";
                }
                break;

            case 'same':
                if ($value !== ($this->data[$ruleParam] ?? null)) {
                    $this->errors[$field] = "$label must match $ruleParam";
                }
                break;

            case 'regex':
                if ($value && !preg_match($ruleParam, $value)) {
                    $this->errors[$field] = "$label format is invalid";
                }
                break;

            case 'date':
                if ($value && !strtotime($value)) {
                    $this->errors[$field] = "$label must be a valid date";
                }
                break;

            case 'nullable':
                // Allow null/empty — no validation needed
                break;

            case 'file':
                if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
                    $this->errors[$field] = "$label must be a valid file";
                }
                break;

            case 'image':
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
                    if (!in_array($_FILES[$field]['type'], $allowed)) {
                        $this->errors[$field] = "$label must be an image (jpg, png, gif, webp)";
                    }
                }
                break;

            case 'max_size':
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    if ($_FILES[$field]['size'] > ((int)$ruleParam * 1024)) {
                        $this->errors[$field] = "$label must not exceed {$ruleParam}KB";
                    }
                }
                break;

            case 'phone':
                if ($value && !preg_match('/^\+?[0-9\s\-()]{7,20}$/', $value)) {
                    $this->errors[$field] = "$label must be a valid phone number";
                }
                break;

            case 'password_strength':
                if ($value && strlen($value) < 8) {
                    $this->errors[$field] = "$label must be at least 8 characters";
                } elseif ($value && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $value)) {
                    $this->errors[$field] = "$label must contain at least one uppercase, one lowercase and one number";
                }
                break;
        }
    }

    public function fails(): bool   { return !empty($this->errors); }
    public function passes(): bool  { return empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function firstError(string $field): ?string { return $this->errors[$field] ?? null; }
    public function validated(): array { return $this->validated; }
}
