<?php
/**
 * Validator Helper Class
 * Handles input validation
 */

class Validator
{
    private $errors = [];

    /**
     * Validate required field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $label Field label for error message
     * @return self
     */
    public function required($field, $value, $label = null)
    {
        $label = $label ?? ucfirst($field);

        if (empty($value) && $value !== '0') {
            $this->errors[$field] = "{$label} is required";
        }

        return $this;
    }

    /**
     * Validate email
     * 
     * @param string $field Field name
     * @param string $value Email value
     * @param string $label Field label
     * @return self
     */
    public function email($field, $value, $label = null)
    {
        $label = $label ?? ucfirst($field);

        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} must be a valid email address";
        }

        return $this;
    }

    /**
     * Validate minimum length
     * 
     * @param string $field Field name
     * @param string $value Field value
     * @param int $min Minimum length
     * @param string $label Field label
     * @return self
     */
    public function min($field, $value, $min, $label = null)
    {
        $label = $label ?? ucfirst($field);

        if (!empty($value) && strlen($value) < $min) {
            $this->errors[$field] = "{$label} must be at least {$min} characters";
        }

        return $this;
    }

    /**
     * Validate maximum length
     * 
     * @param string $field Field name
     * @param string $value Field value
     * @param int $max Maximum length
     * @param string $label Field label
     * @return self
     */
    public function max($field, $value, $max, $label = null)
    {
        $label = $label ?? ucfirst($field);

        if (!empty($value) && strlen($value) > $max) {
            $this->errors[$field] = "{$label} must not exceed {$max} characters";
        }

        return $this;
    }

    /**
     * Validate numeric value
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $label Field label
     * @return self
     */
    public function numeric($field, $value, $label = null)
    {
        $label = $label ?? ucfirst($field);

        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = "{$label} must be a number";
        }

        return $this;
    }

    /**
     * Validate phone number (basic)
     * 
     * @param string $field Field name
     * @param string $value Phone value
     * @param string $label Field label
     * @return self
     */
    public function phone($field, $value, $label = null)
    {
        $label = $label ?? ucfirst($field);

        if (!empty($value) && !preg_match('/^[\d\s\-\+\(\)]+$/', $value)) {
            $this->errors[$field] = "{$label} must be a valid phone number";
        }

        return $this;
    }

    /**
     * Validate date format
     * 
     * @param string $field Field name
     * @param string $value Date value
     * @param string $format Date format (default: Y-m-d)
     * @param string $label Field label
     * @return self
     */
    public function date($field, $value, $format = 'Y-m-d', $label = null)
    {
        $label = $label ?? ucfirst($field);

        if (!empty($value)) {
            $d = DateTime::createFromFormat($format, $value);
            if (!$d || $d->format($format) !== $value) {
                $this->errors[$field] = "{$label} must be a valid date";
            }
        }

        return $this;
    }

    /**
     * Validate match (e.g., password confirmation)
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param mixed $matchValue Value to match
     * @param string $label Field label
     * @return self
     */
    public function match($field, $value, $matchValue, $label = null)
    {
        $label = $label ?? ucfirst($field);

        if ($value !== $matchValue) {
            $this->errors[$field] = "{$label} does not match";
        }

        return $this;
    }

    /**
     * Validate strong password
     * 
     * @param string $field
     * @param string $value
     * @param string $label
     * @return self
     */
    public function strongPassword($field, $value, $label = null)
    {
        $label = $label ?? ucfirst($field);

        if (empty($value))
            return $this;

        $errors = [];
        if (strlen($value) < 8)
            $errors[] = "at least 8 characters";
        if (!preg_match('/[A-Z]/', $value))
            $errors[] = "one uppercase letter";
        if (!preg_match('/[a-z]/', $value))
            $errors[] = "one lowercase letter";
        if (!preg_match('/[0-9]/', $value))
            $errors[] = "one number";
        if (!preg_match('/[^A-Za-z0-9]/', $value))
            $errors[] = "one special character";

        if (!empty($errors)) {
            $this->errors[$field] = "{$label} must contain " . implode(', ', $errors);
        }

        return $this;
    }

    /**
     * Check if validation passed
     * 
     * @return bool
     */
    public function passes()
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     * 
     * @return bool
     */
    public function fails()
    {
        return !$this->passes();
    }

    /**
     * Get all errors
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get first error
     * 
     * @return string|null
     */
    public function getFirstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Add custom error
     * 
     * @param string $field Field name
     * @param string $message Error message
     * @return self
     */
    public function addError($field, $message)
    {
        $this->errors[$field] = $message;
        return $this;
    }
}
