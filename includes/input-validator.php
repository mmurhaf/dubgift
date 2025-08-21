<?php
/**
 * Input Validation and Sanitization Class
 */

class InputValidator {
    
    /**
     * Sanitize string input
     */
    public static function sanitizeString($input, $maxLength = null) {
        $sanitized = trim($input);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        
        if ($maxLength && strlen($sanitized) > $maxLength) {
            $sanitized = substr($sanitized, 0, $maxLength);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize email
     */
    public static function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize URL
     */
    public static function sanitizeUrl($url) {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }
    
    /**
     * Sanitize integer
     */
    public static function sanitizeInt($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Sanitize float
     */
    public static function sanitizeFloat($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate phone number (basic)
     */
    public static function validatePhone($phone) {
        $phone = preg_replace('/[^0-9+\-\s]/', '', $phone);
        return preg_match('/^[\+]?[0-9\-\s]{7,15}$/', $phone);
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequired($data, $requiredFields) {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        return $errors;
    }
    
    /**
     * Clean HTML content (for rich text)
     */
    public static function cleanHtml($html, $allowedTags = '<p><br><strong><em><ul><ol><li>') {
        $html = strip_tags($html, $allowedTags);
        $html = preg_replace('/javascript:/i', '', $html);
        $html = preg_replace('/on\w+\s*=/i', '', $html);
        return $html;
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes, $maxSize) {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = 'File is too large';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = 'File upload was interrupted';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = 'No file was uploaded';
                    break;
                default:
                    $errors[] = 'File upload failed';
            }
            return $errors;
        }
        
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size';
        }
        
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, $allowedTypes)) {
            $errors[] = 'File type not allowed';
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        if (isset($allowedMimes[$extension]) && $mimeType !== $allowedMimes[$extension]) {
            $errors[] = 'File type does not match extension';
        }
        
        return $errors;
    }
    
    /**
     * Validate and sanitize array of data
     */
    public static function validateAndSanitize($data, $rules) {
        $result = [];
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Check if required
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                continue;
            }
            
            if (!empty($value)) {
                // Apply sanitization
                switch ($rule['type']) {
                    case 'email':
                        $value = self::sanitizeEmail($value);
                        if (isset($rule['validate']) && $rule['validate'] && !self::validateEmail($value)) {
                            $errors[$field] = 'Invalid email format';
                        }
                        break;
                        
                    case 'string':
                        $maxLength = $rule['max_length'] ?? null;
                        $value = self::sanitizeString($value, $maxLength);
                        break;
                        
                    case 'int':
                        $value = self::sanitizeInt($value);
                        if (isset($rule['min']) && $value < $rule['min']) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be at least ' . $rule['min'];
                        }
                        if (isset($rule['max']) && $value > $rule['max']) {
                            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must not exceed ' . $rule['max'];
                        }
                        break;
                        
                    case 'float':
                        $value = self::sanitizeFloat($value);
                        break;
                        
                    case 'url':
                        $value = self::sanitizeUrl($value);
                        if (isset($rule['validate']) && $rule['validate'] && !self::validateUrl($value)) {
                            $errors[$field] = 'Invalid URL format';
                        }
                        break;
                        
                    case 'html':
                        $allowedTags = $rule['allowed_tags'] ?? '<p><br><strong><em><ul><ol><li>';
                        $value = self::cleanHtml($value, $allowedTags);
                        break;
                }
                
                $result[$field] = $value;
            }
        }
        
        return ['data' => $result, 'errors' => $errors];
    }
}
?>
