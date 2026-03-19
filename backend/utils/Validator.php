<?php
// backend/utils/Validator.php

class Validator {

    /* =========================================================
       1. VALIDATION KIỂU SỐ (NUMBER VALIDATION)
       ========================================================= */
    
    // Kiểm tra có phải là số không (Is Numeric)?
    public static function isNumeric($value) {
        if (!is_numeric($value)) return "Giá trị bắt buộc phải là số.";
        return true;
    }

    // Kiểm tra khoảng giá trị (Range Check)
    public static function checkNumberRange($value, $min, $max) {
        $check = self::isNumeric($value);
        if ($check !== true) return $check;
        if ($value < $min || $value > $max) return "Giá trị phải nằm trong khoảng từ $min đến $max.";
        return true;
    }

    // Kiểm tra số nguyên/số thập phân (Integer/Decimal Check)
    public static function checkIntOrDecimal($value, $type = 'int') {
        $check = self::isNumeric($value);
        if ($check !== true) return $check;

        if ($type === 'int' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            return "Giá trị bắt buộc phải là số nguyên.";
        }
        if ($type === 'decimal' && filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            return "Giá trị bắt buộc phải là số thập phân.";
        }
        return true;
    }

    // Kiểm tra số dương/âm (Positive/Negative Check)
    public static function checkPositiveNegative($value, $type = 'positive') {
        $check = self::isNumeric($value);
        if ($check !== true) return $check;

        if ($type === 'positive' && $value <= 0) return "Giá trị phải là số dương (> 0).";
        if ($type === 'negative' && $value >= 0) return "Giá trị phải là số âm (< 0).";
        return true;
    }

    // Kiểm tra định dạng số (Number Format Check - VD: Số điện thoại)
    public static function checkNumberFormat($value, $pattern = '/^[0-9]{10,11}$/') {
        if (!preg_match($pattern, $value)) return "Định dạng số không hợp lệ.";
        return true;
    }

    /* =========================================================
       2. VALIDATION KIỂU CHUỖI (STRING VALIDATION)
       ========================================================= */

    // Kiểm tra không rỗng/có giá trị (Not Null/Empty Check)
    public static function isNotEmpty($value) {
        if (trim($value) === '') return "Dữ liệu không được để rỗng.";
        return true;
    }

    // Kiểm tra độ dài tối thiểu/tối đa (Min/Max Length Check)
    public static function checkStringLength($value, $min, $max) {
        $check = self::isNotEmpty($value);
        if ($check !== true) return $check;

        $length = mb_strlen(trim($value), 'UTF-8');
        if ($length < $min) return "Độ dài tối thiểu là $min ký tự.";
        if ($length > $max) return "Độ dài tối đa là $max ký tự.";
        return true;
    }

    // Kiểm tra định dạng (Format Check - Regex) VD: Email
    public static function checkRegex($value, $pattern, $errorMessage = "Định dạng không hợp lệ.") {
        if (!preg_match($pattern, $value)) return $errorMessage;
        return true;
    }

    // Kiểm tra ký tự cho phép (Allowed Characters Check) VD: Chỉ chứa chữ và số
    public static function checkAllowedCharacters($value) {
        // Chỉ cho phép chữ cái (cả tiếng Việt), số và khoảng trắng
        $pattern = '/^[\p{L}0-9\s]+$/u';
        if (!preg_match($pattern, $value)) return "Chỉ cho phép nhập chữ cái, số và khoảng trắng.";
        return true;
    }

    /* =========================================================
       3. VALIDATION NGÀY THÁNG (DATE VALIDATION)
       ========================================================= */

    // Kiểm tra định dạng ngày tháng hợp lệ & Ngày tháng hợp lệ thực tế (Valid Date)
    // Nó sẽ báo lỗi nếu bạn nhập 31/02/2026 (ngày không có thực)
    public static function isValidDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        if (!($d && $d->format($format) === $date)) {
            return "Ngày tháng không hợp lệ hoặc sai định dạng ($format).";
        }
        return true;
    }

    // Kiểm tra Start Date <= End Date
    public static function checkStartEndDate($startDate, $endDate, $format = 'Y-m-d') {
        $checkStart = self::isValidDate($startDate, $format);
        $checkEnd = self::isValidDate($endDate, $format);
        if ($checkStart !== true) return "Start Date: " . $checkStart;
        if ($checkEnd !== true) return "End Date: " . $checkEnd;

        $start = DateTime::createFromFormat($format, $startDate);
        $end = DateTime::createFromFormat($format, $endDate);

        if ($start > $end) return "Ngày bắt đầu không được lớn hơn ngày kết thúc.";
        return true;
    }

    // Kiểm tra ngày trong tương lai/quá khứ (Future/Past Date Check)
    public static function checkFuturePastDate($date, $type = 'future', $format = 'Y-m-d') {
        $check = self::isValidDate($date, $format);
        if ($check !== true) return $check;

        $targetDate = DateTime::createFromFormat($format, $date);
        $today = new DateTime('today');

        if ($type === 'future' && $targetDate <= $today) {
            return "Ngày được chọn phải là một ngày trong tương lai.";
        }
        if ($type === 'past' && $targetDate >= $today) {
            return "Ngày được chọn phải là một ngày trong quá khứ.";
        }
        return true;
    }

    /* =========================================================
       4. VALIDATION TUỔI (AGE VALIDATION)
       ========================================================= */

    // Tuổi tối thiểu/tối đa (Min/Max Age Check) & Phải là số nguyên dương
    public static function checkAge($age, $minAge = 1, $maxAge = 120) {
        // Kiểm tra phải là số nguyên (Integer)
        $isInt = self::checkIntOrDecimal($age, 'int');
        if ($isInt !== true) return "Tuổi phải là số nguyên.";

        // Kiểm tra phải là số dương (Positive)
        $isPositive = self::checkPositiveNegative($age, 'positive');
        if ($isPositive !== true) return "Tuổi phải là số nguyên dương.";

        // Kiểm tra khoảng tuổi (Min/Max)
        $isRangeValid = self::checkNumberRange($age, $minAge, $maxAge);
        if ($isRangeValid !== true) return "Độ tuổi cho phép là từ $minAge đến $maxAge tuổi.";

        return true;
    }
}
?>