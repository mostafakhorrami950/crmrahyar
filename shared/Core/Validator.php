<?php
namespace Shared\Core;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string ...$fields): self
    {
        foreach ($fields as $field) {
            if (!isset($this->data[$field]) || (is_string($this->data[$field]) && trim($this->data[$field]) === '')) {
                $this->errors[$field][] = "{$field} الزامی است.";
            }
        }
        return $this;
    }

    public function email(string $field): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "فرمت ایمیل نامعتبر است.";
        }
        return $this;
    }

    public function min(string $field, int $min): self
    {
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && $this->data[$field] < $min) {
            $this->errors[$field][] = "{$field} باید حداقل {$min} باشد.";
        }
        return $this;
    }

    public function max(string $field, int $max): self
    {
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && $this->data[$field] > $max) {
            $this->errors[$field][] = "{$field} باید حداکثر {$max} باشد.";
        }
        return $this;
    }

    public function minLength(string $field, int $min): self
    {
        if (!empty($this->data[$field]) && mb_strlen($this->data[$field]) < $min) {
            $this->errors[$field][] = "{$field} باید حداقل {$min} کاراکتر باشد.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max): self
    {
        if (!empty($this->data[$field]) && mb_strlen($this->data[$field]) > $max) {
            $this->errors[$field][] = "{$field} باید حداکثر {$max} کاراکتر باشد.";
        }
        return $this;
    }

    public function numeric(string $field): self
    {
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = "{$field} باید عددی باشد.";
        }
        return $this;
    }

    public function date(string $field): self
    {
        if (!empty($this->data[$field])) {
            $d = \DateTime::createFromFormat('Y-m-d', $this->data[$field]);
            if (!$d || $d->format('Y-m-d') !== $this->data[$field]) {
                $this->errors[$field][] = "فرمت تاریخ {$field} نامعتبر است.";
            }
        }
        return $this;
    }

    public function in(string $field, array $allowed): self
    {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $allowed)) {
            $this->errors[$field][] = "مقدار {$field} نامعتبر است.";
        }
        return $this;
    }

    public function unique(string $field, string $table, string $column = '', ?int $exceptId = null): self
    {
        if (!empty($this->data[$field])) {
            $col = $column ?: $field;
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as cnt FROM {$table} WHERE {$col} = :val";
            $params = [':val' => $this->data[$field]];
            if ($exceptId) {
                $sql .= " AND id != :eid";
                $params[':eid'] = $exceptId;
            }
            try {
                $result = $db->fetch($sql, $params);
                if ($result && $result->cnt > 0) {
                    $this->errors[$field][] = "مقدار {$field} قبلاً ثبت شده است.";
                }
            } catch (\Exception $e) {
                // Table may not exist
            }
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) return $fieldErrors[0];
        }
        return null;
    }
}