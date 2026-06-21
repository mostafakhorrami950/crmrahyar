<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class ImportController
{
    // Available fields for contact import mapping
    public static function getContactFields(): array
    {
        return [
            'skip' => '— نادیده گرفتن —',
            'full_name' => '👤 نام کامل',
            'first_name' => '👤 نام',
            'last_name' => '👤 نام خانوادگی',
            'phone' => '📞 تلفن',
            'email' => '📧 ایمیل',
            'company' => '🏢 شرکت',
            'company_phone' => '📞 تلفن شرکت',
            'national_code' => '🔢 کد ملی',
            'passport_number' => '📘 شماره پاسپورت',
            'address' => '📍 آدرس',
            'source' => '🎯 منبع آشنایی',
            'tags' => '🏷️ برچسب‌ها',
            'notes' => '📝 یادداشت‌ها',
        ];
    }

    // ─── Step 1: Show upload form ──────────────────
    public function showForm(): void
    {
        Auth::requirePermission('contacts.create');
        View::render('contacts/import', [
            'title' => 'ایمپورت مخاطبین',
            'contactFields' => self::getContactFields(),
        ]);
    }

    // ─── Step 2: Upload file and show column mapping ──
    public function upload(): void
    {
        Auth::requirePermission('contacts.create');
        
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('danger', 'فایل آپلود نشد. لطفاً دوباره تلاش کنید.');
            View::redirect('/contacts/import');
            return;
        }

        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
            Session::setFlash('danger', 'فرمت فایل مجاز نیست. فقط CSV و XLSX پشتیبانی می‌شود.');
            View::redirect('/contacts/import');
            return;
        }

        // Max 5MB
        if ($file['size'] > 5 * 1024 * 1024) {
            Session::setFlash('danger', 'حجم فایل نباید بیشتر ا 5 مگابایت باشد.');
            View::redirect('/contacts/import');
            return;
        }

        // Save temp file
        $tmpPath = $file['tmp_name'];
        $uploadDir = __DIR__ . '/../storage/uploads/imports/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $savedName = 'import_' . time() . '_' . uniqid() . '.' . $ext;
        $savedPath = $uploadDir . $savedName;
        move_uploaded_file($tmpPath, $savedPath);

        // Parse file
        try {
            if ($ext === 'csv') {
                $data = self::parseCSV($savedPath);
            } else {
                $data = self::parseXLSX($savedPath);
            }
        } catch (\Exception $e) {
            unlink($savedPath);
            Session::setFlash('danger', 'خطا در خواندن فایل: ' . $e->getMessage());
            View::redirect('/contacts/import');
            return;
        }

        if (empty($data) || count($data) < 2) {
            unlink($savedPath);
            Session::setFlash('danger', 'فایل خالی است یا فقط هدر دارد.');
            View::redirect('/contacts/import');
            return;
        }

        $headers = $data[0]; // First row = headers
        $sampleRows = array_slice($data, 1, 5); // Show 5 sample rows
        $totalRows = count($data) - 1;

        // Auto-detect mapping
        $autoMap = self::autoDetectMapping($headers);

        // Save file info in session for step 3
        Session::set('import_file', $savedPath);
        Session::set('import_file_name', $file['name']);
        Session::set('import_ext', $ext);

        // Load categories for selection
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT id, name, color FROM contact_categories ORDER BY name") ?: [];

        View::render('contacts/import_mapping', [
            'title' => 'نگاشت ستون‌ها',
            'headers' => $headers,
            'sampleRows' => $sampleRows,
            'totalRows' => $totalRows,
            'contactFields' => self::getContactFields(),
            'autoMap' => $autoMap,
            'fileName' => $file['name'],
            'categories' => $categories,
        ]);
    }

    // ─── Step 3: Preview and confirm import ────────
    public function preview(): void
    {
        // Suppress ALL PHP warnings/notices for this AJAX endpoint
        $oldLevel = error_reporting(0);
        set_error_handler(function() { return true; });
        set_exception_handler(function($e) {
            echo json_encode(['success' => false, 'message' => 'خطای سرور: ' . $e->getMessage()]);
            exit;
        });
        while (ob_get_level()) ob_end_clean();
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
        Auth::requirePermission('contacts.create');
        
        $savedPath = Session::get('import_file');
        if (!$savedPath || !file_exists($savedPath)) {
            echo json_encode(['success' => false, 'message' => 'فایل یافت نشد. دوباره آپلود کنید.']);
            exit;
        }

        $ext = Session::get('import_ext', 'csv');
        $mapping = $_POST['mapping'] ?? [];

        if (empty($mapping)) {
            echo json_encode(['success' => false, 'message' => 'نگاشت ستون‌ها مشخص نشده.']);
            exit;
        }

        try {
            if ($ext === 'csv') {
                $data = self::parseCSV($savedPath);
            } else {
                $data = self::parseXLSX($savedPath);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
            exit;
        }

        if (empty($data) || count($data) < 2) {
            echo json_encode(['success' => false, 'message' => 'فایل خالی است یا فقط هدر دارد.']);
            exit;
        }

        $headers = $data[0];
        $rows = array_slice($data, 1);
        $preview = [];

        foreach (array_slice($rows, 0, 10) as $row) {
            $mapped = [];
            foreach ($mapping as $colIndex => $fieldName) {
                if ($fieldName === 'skip') continue;
                $mapped[$fieldName] = $row[$colIndex] ?? '';
            }
            $preview[] = $mapped;
        }

        echo json_encode([
            'success' => true,
            'preview' => $preview,
            'total' => count($rows),
        ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
        }
        exit;
    }

    // ─── Step 4: Execute import ────────────────────
    public function execute(): void
    {
        $oldLevel = error_reporting(0);
        set_error_handler(function() { return true; });
        set_exception_handler(function($e) {
            echo json_encode(['success' => false, 'message' => 'خطای سرور: ' . $e->getMessage()]);
            exit;
        });
        while (ob_get_level()) ob_end_clean();
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
        Auth::requirePermission('contacts.create');

        $savedPath = Session::get('import_file');
        if (!$savedPath || !file_exists($savedPath)) {
            echo json_encode(['success' => false, 'message' => 'فایل یافت نشد.']);
            exit;
        }

        $ext = Session::get('import_ext', 'csv');
        $mapping = $_POST['mapping'] ?? [];
        $categoryId = (int)($_POST['category_id'] ?? 0);

        if (empty($mapping)) {
            echo json_encode(['success' => false, 'message' => 'نگاشت مشخص نشده.']);
            exit;
        }

        // Check which field is mapped to know what to combine
        $hasFirstName = in_array('first_name', $mapping);
        $hasLastName = in_array('last_name', $mapping);
        $hasFullName = in_array('full_name', $mapping);

        try {
            if ($ext === 'csv') {
                $data = self::parseCSV($savedPath);
            } else {
                $data = self::parseXLSX($savedPath);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
            exit;
        }

        $headers = $data[0];
        $rows = array_slice($data, 1);
        $db = Database::getInstance();
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $rowIndex => $row) {
            try {
                $mapped = [];
                foreach ($mapping as $colIndex => $fieldName) {
                    if ($fieldName === 'skip') continue;
                    $mapped[$fieldName] = trim($row[$colIndex] ?? '');
                }

                // Build full_name from first/last if needed
                $fullName = '';
                if ($hasFullName && !empty($mapped['full_name'])) {
                    $fullName = $mapped['full_name'];
                } elseif ($hasFirstName || $hasLastName) {
                    $firstName = $mapped['first_name'] ?? '';
                    $lastName = $mapped['last_name'] ?? '';
                    $fullName = trim($firstName . ' ' . $lastName);
                }

                // Parse multiple phones from cell
                $phoneRaw = $mapped['phone'] ?? '';
                $phones = self::splitMultiValues($phoneRaw, 'phone');
                if (empty($phones)) $phones = [''];

                // Skip rows with no name AND no phones
                if (empty($fullName) && empty($phones[0])) {
                    $skipped++;
                    continue;
                }

                // Common data shared across all contacts from this row
                $commonData = [
                    'full_name' => $fullName ?: 'بدون نام',
                    'email' => $mapped['email'] ?? '',
                    'company' => $mapped['company'] ?? '',
                    'company_phone' => $mapped['company_phone'] ?? '',
                    'national_code' => $mapped['national_code'] ?? '',
                    'passport_number' => $mapped['passport_number'] ?? '',
                    'address' => $mapped['address'] ?? '',
                    'source' => $mapped['source'] ?? '',
                    'tags' => $mapped['tags'] ?? '',
                    'notes' => $mapped['notes'] ?? '',
                    'category_id' => $categoryId > 0 ? $categoryId : null,
                    'created_by' => Auth::id(),
                ];

                // Create one contact per phone number
                foreach ($phones as $phone) {
                    $insertData = $commonData;
                    $insertData['phone'] = $phone;

                    // Check for duplicate phone (only if phone is not empty)
                    if (!empty($phone)) {
                        $existing = $db->fetch("SELECT id FROM contacts WHERE phone = :phone LIMIT 1", [':phone' => $phone]);
                        if ($existing) {
                            $skipped++;
                            continue;
                        }
                    }

                    $db->insert('contacts', $insertData);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "ردیف " . ($rowIndex + 2) . ": " . $e->getMessage();
            }
        }

        // Clean up
        if (file_exists($savedPath)) unlink($savedPath);
        Session::remove('import_file');
        Session::remove('import_file_name');
        Session::remove('import_ext');

        ActivityLog::log('contacts.import', 'contact', 0, "{$imported} مخاطب ایمپورت شد");

        echo json_encode([
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 10),
            'message' => "{$imported} مخاطب با موفقیت ایمپورت شد" . ($skipped > 0 ? " ({$skipped} ردیف نادیده گرفته شد)" : ''),
        ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
        }
        exit;
    }

    // ═══════════════════════════════════════════════════
    // Split multi-value cells (e.g. "0912xxx,0913xxx" or "0912xxx\n0913xxx")
    // ═══════════════════════════════════════════════════
    private static function splitMultiValues(string $value, string $type = 'phone'): array
    {
        $value = trim($value);
        if (empty($value)) return [];
        
        // Split by common delimiters: ::: , comma, semicolon, newline, pipe, slash, dash
        $value = preg_replace('/:{2,}/u', ',', $value); // Convert ::: to comma first
        $parts = preg_split('/[,;|\n\r\/~]+/u', $value);
        $parts = array_map('trim', $parts);
        $parts = array_filter($parts, function($v) { return $v !== ''; });
        
        if ($type === 'phone') {
            // Clean phone numbers: remove spaces, dashes, etc.
            $parts = array_map(function($p) {
                $p = preg_replace('/[\s\-\(\)]+/', '', $p);
                // Normalize Persian/Arabic digits
                $p = strtr($p, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
                $p = strtr($p, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
                return $p;
            }, $parts);
        }
        
        return array_values($parts);
    }

    // ═══════════════════════════════════════════════════
    // CSV Parser
    // ═══════════════════════════════════════════════════
    private static function parseCSV(string $filePath): array
    {
        $rows = [];
        
        // Read entire file to detect encoding
        $raw = file_get_contents($filePath);
        if ($raw === false) throw new \Exception('خطا در خواندن فایل CSV');
        
        // Detect and convert encoding to UTF-8
        if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
            $raw = substr($raw, 3); // Remove BOM
        }
        
        // Check if already valid UTF-8
        if (!mb_check_encoding($raw, 'UTF-8')) {
            // Try common encodings for Persian/Arabic files
            $detected = mb_detect_encoding($raw, ['Windows-1256', 'UTF-8', 'ISO-8859-1', 'Windows-1251', 'Windows-1252'], true);
            if ($detected && $detected !== 'UTF-8') {
                $raw = mb_convert_encoding($raw, 'UTF-8', $detected);
            } else {
                // Last resort
                $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1256');
            }
        }
        
        // Write converted content to temp file
        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_import_');
        file_put_contents($tmpFile, $raw);

        $handle = fopen($tmpFile, 'r');
        if (!$handle) { unlink($tmpFile); throw new \Exception('خطا در باز کردن فایل CSV'); }

        // Try to detect delimiter from first line
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = ',';
        if (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        } elseif (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = array_map(function($cell) {
                return trim($cell);
            }, $row);
            if (empty(array_filter($row))) continue;
            $rows[] = $row;
        }

        fclose($handle);
        unlink($tmpFile);
        return $rows;
    }

    // ═══════════════════════════════════════════════════
    // XLSX Parser (lightweight, no external library)
    // ═══════════════════════════════════════════════════
    private static function parseXLSX(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            throw new \Exception('PHP ZipArchive extension required for XLSX files');
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception('خطا در باز کردن فایل XLSX');
        }

        // Read shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $ssDoc = new \SimpleXMLElement($ssXml);
            foreach ($ssDoc->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    // Rich text - concatenate all t elements
                    $text = '';
                    foreach ($si->r as $r) {
                        if (isset($r->t)) $text .= (string)$r->t;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        // Read first sheet
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new \Exception('شیتی در فایل XLSX یافت نشد');
        }

        $doc = new \SimpleXMLElement($sheetXml);
        $rows = [];

        if (!isset($doc->sheetData) || !isset($doc->sheetData->row)) {
            $zip->close();
            return $rows;
        }

        foreach ($doc->sheetData->row as $row) {
            $rowData = [];
            if (!isset($row->c)) continue;
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r'];
                $type = (string)$cell['t'];
                $value = '';

                if ($type === 's') {
                    // Shared string
                    $index = (int)$cell->v;
                    $value = $sharedStrings[$index] ?? '';
                } elseif (isset($cell->v)) {
                    $value = (string)$cell->v;
                }

                // Get column index from ref (A1, B1, etc.)
                $colIndex = self::columnRefToIndex($ref);
                $rowData[$colIndex] = $value;
            }

            // Fill gaps
            if (!empty($rowData)) {
                $maxCol = max(array_keys($rowData));
                $filled = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $filled[] = $rowData[$i] ?? '';
                }
                $rows[] = $filled;
            }
        }

        $zip->close();
        return $rows;
    }

    // Convert column reference (A1, B1, AA1) to zero-based index
    private static function columnRefToIndex(string $ref): int
    {
        $col = preg_replace('/[0-9]/', '', $ref);
        $index = 0;
        $len = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord(strtoupper($col[$i])) - 64);
        }
        return $index - 1;
    }

    // ═══════════════════════════════════════════════════
    // Auto-detect column mapping based on header names
    // ═══════════════════════════════════════════════════
    private static function autoDetectMapping(array $headers): array
    {
        $map = [];
        $fieldPatterns = [
            'full_name' => ['نام', 'نام کامل', 'نام و نام خانوادگی', 'name', 'full_name', 'fullname'],
            'first_name' => ['نام', 'first name', 'first_name', 'firstname'],
            'last_name' => ['نام خانوادگی', 'نام خانوادگی', 'last name', 'last_name', 'lastname', 'family', 'surname'],
            'phone' => ['تلفن', 'موبایل', 'شماره', 'phone', 'mobile', 'tel', 'cell', 'شماره تماس', 'موبایل', 'همراه'],
            'email' => ['ایمیل', 'پست الکترونیک', 'email', 'mail', 'e-mail'],
            'company' => ['شرکت', 'سازمان', 'company', 'organization', 'org', 'firma'],
            'national_code' => ['کد ملی', 'کدملی', 'national code', 'national_code', 'nationalcode', 'melicode'],
            'passport_number' => ['پاسپورت', 'شماره پاسپورت', 'passport', 'passport_number'],
            'address' => ['آدرس', 'نشانی', 'address', 'addr'],
            'source' => ['منبع', 'منبع آشنایی', 'source', 'how_found'],
            'tags' => ['برچسب', 'تگ', 'tags', 'tag', 'label'],
            'notes' => ['یادداشت', 'توضیحات', 'notes', 'note', 'comment', 'description'],
        ];

        foreach ($headers as $index => $header) {
            $h = mb_strtolower(trim($header));
            $matched = 'skip';
            
            foreach ($fieldPatterns as $field => $patterns) {
                foreach ($patterns as $pattern) {
                    if ($h === mb_strtolower($pattern) || strpos($h, mb_strtolower($pattern)) !== false) {
                        $matched = $field;
                        break 2;
                    }
                }
            }
            
            $map[$index] = $matched;
        }

        return $map;
    }
}