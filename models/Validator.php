<?php
/**
 * Input Validation and Sanitization Class
 *
 * Provides comprehensive validation and sanitization methods
 */
class Validator {
    private $errors = [];

    /**
     * Validate required fields
     */
    public function validateRequired($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $this->errors[] = "El campo '$field' es requerido.";
            }
        }
        return $this;
    }

    /**
     * Validate email format
     */
    public function validateEmail($email, $fieldName = 'email') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "El campo '$fieldName' debe ser una dirección de correo electrónico válida.";
        }
        return $this;
    }

    /**
     * Validate password strength
     */
    public function validatePassword($password, $fieldName = 'password') {
        if (strlen($password) < 8) {
            $this->errors[] = "El campo '$fieldName' debe tener al menos 8 caracteres.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors[] = "El campo '$fieldName' debe contener al menos una letra mayúscula.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $this->errors[] = "El campo '$fieldName' debe contener al menos una letra minúscula.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $this->errors[] = "El campo '$fieldName' debe contener al menos un número.";
        }
        return $this;
    }

    /**
     * Validate phone number format
     */
    public function validatePhone($phone, $fieldName = 'phone') {
        // Mexican phone number validation (10 digits)
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            $this->errors[] = "El campo '$fieldName' debe ser un número de teléfono válido (10 dígitos).";
        }
        return $this;
    }

    /**
     * Validate numeric value
     */
    public function validateNumeric($value, $fieldName, $min = null, $max = null) {
        if (!is_numeric($value)) {
            $this->errors[] = "El campo '$fieldName' debe ser un número válido.";
            return $this;
        }

        $num = floatval($value);

        if ($min !== null && $num < $min) {
            $this->errors[] = "El campo '$fieldName' debe ser mayor o igual a $min.";
        }

        if ($max !== null && $num > $max) {
            $this->errors[] = "El campo '$fieldName' debe ser menor o igual a $max.";
        }

        return $this;
    }

    /**
     * Validate string length
     */
    public function validateLength($value, $fieldName, $min = null, $max = null) {
        $length = strlen(trim($value));

        if ($min !== null && $length < $min) {
            $this->errors[] = "El campo '$fieldName' debe tener al menos $min caracteres.";
        }

        if ($max !== null && $length > $max) {
            $this->errors[] = "El campo '$fieldName' no debe exceder $max caracteres.";
        }

        return $this;
    }

    /**
     * Validate against whitelist of values
     */
    public function validateInArray($value, $fieldName, $allowedValues) {
        if (!in_array($value, $allowedValues)) {
            $this->errors[] = "El campo '$fieldName' debe ser uno de los siguientes valores: " . implode(', ', $allowedValues);
        }
        return $this;
    }

    /**
     * Sanitize input data
     */
    public function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        // Remove null bytes
        $data = str_replace(chr(0), '', $data);

        // Convert special characters to HTML entities
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        // Trim whitespace
        return trim($data);
    }

    /**
     * Sanitize email
     */
    public function sanitizeEmail($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize numeric input
     */
    public function sanitizeNumeric($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Check if validation passed
     */
    public function isValid() {
        return empty($this->errors);
    }

    /**
     * Clear errors
     */
    public function clearErrors() {
        $this->errors = [];
        return $this;
    }

    /**
     * Get first error
     */
    public function getFirstError() {
        return !empty($this->errors) ? $this->errors[0] : null;
    }

    /**
     * Validate user registration data
     */
    public function validateRegistration($data) {
        return $this->clearErrors()
            ->validateRequired($data, ['username', 'email', 'password', 'confirm_password', 'user_type'])
            ->validateEmail($data['email'])
            ->validatePassword($data['password'])
            ->validateLength($data['username'], 'username', 3, 50)
            ->validateInArray($data['user_type'], 'user_type', ['buyer', 'seller', 'agent'])
            ->isValid();
    }

    /**
     * Validate property data
     */
    public function validateProperty($data) {
        return $this->clearErrors()
            ->validateRequired($data, ['title', 'price', 'location', 'property_type'])
            ->validateLength($data['title'], 'title', 5, 255)
            ->validateNumeric($data['price'], 'price', 0)
            ->validateLength($data['location'], 'location', 3, 255)
            ->validateInArray($data['property_type'], 'property_type', ['casa', 'departamento', 'terreno', 'local-comercial'])
            ->isValid();
    }
}
?>