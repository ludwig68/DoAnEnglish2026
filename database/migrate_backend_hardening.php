<?php
declare(strict_types=1);

require_once __DIR__ . '/../backend/config/db.php';

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);

    return (bool) $stmt->fetchColumn();
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);

    return (bool) $stmt->fetchColumn();
}

function indexExists(PDO $pdo, string $table, string $index): bool
{
    $stmt = $pdo->query("SHOW INDEX FROM `{$table}`");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (($row['Key_name'] ?? '') === $index) {
            return true;
        }
    }

    return false;
}

function foreignKeyExists(PDO $pdo, string $table, string $constraint): bool
{
    $stmt = $pdo->prepare("
        SELECT CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND CONSTRAINT_NAME = ?
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        LIMIT 1
    ");
    $stmt->execute([$table, $constraint]);

    return (bool) $stmt->fetchColumn();
}

function executeIfMissing(PDO $pdo, bool $condition, string $sql): void
{
    if ($condition) {
        $pdo->exec($sql);
    }
}

try {
    $pdo->beginTransaction();

    if (!tableExists($pdo, 'course_materials')) {
        $pdo->exec("
            CREATE TABLE `course_materials` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `course_id` INT NOT NULL,
                `lesson_id` INT DEFAULT NULL,
                `schedule_id` INT DEFAULT NULL,
                `title` VARCHAR(255) NOT NULL,
                `file_type` VARCHAR(50) NOT NULL DEFAULT 'document',
                `file_url` VARCHAR(500) NOT NULL,
                `file_size` INT NOT NULL DEFAULT 0,
                `original_name` VARCHAR(255) DEFAULT NULL,
                `uploaded_by` INT DEFAULT NULL,
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_course_materials_course` (`course_id`),
                KEY `idx_course_materials_lesson` (`lesson_id`),
                KEY `idx_course_materials_schedule` (`schedule_id`),
                KEY `idx_course_materials_uploaded_by` (`uploaded_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    executeIfMissing($pdo, !indexExists($pdo, 'class_details', 'uq_class_details_id_class'), "
        ALTER TABLE `class_details`
        ADD UNIQUE KEY `uq_class_details_id_class` (`id`, `class_id`)
    ");

    executeIfMissing($pdo, !indexExists($pdo, 'enrollments', 'uq_enrollments_student_class'), "
        ALTER TABLE `enrollments`
        ADD UNIQUE KEY `uq_enrollments_student_class` (`student_id`, `class_id`)
    ");

    executeIfMissing($pdo, !indexExists($pdo, 'enrollments', 'idx_enrollments_detail_class'), "
        ALTER TABLE `enrollments`
        ADD KEY `idx_enrollments_detail_class` (`class_detail_id`, `class_id`)
    ");

    if (foreignKeyExists($pdo, 'enrollments', 'fk_enrollments_class_detail')) {
        $pdo->exec("ALTER TABLE `enrollments` DROP FOREIGN KEY `fk_enrollments_class_detail`");
    }
    $pdo->exec("
        ALTER TABLE `enrollments`
        ADD CONSTRAINT `fk_enrollments_class_detail`
        FOREIGN KEY (`class_detail_id`, `class_id`)
        REFERENCES `class_details` (`id`, `class_id`)
    ");

    executeIfMissing($pdo, !indexExists($pdo, 'leave_requests', 'idx_class'), "
        ALTER TABLE `leave_requests`
        ADD KEY `idx_class` (`class_id`)
    ");

    executeIfMissing($pdo, !indexExists($pdo, 'academic_warnings', 'idx_schedule'), "
        ALTER TABLE `academic_warnings`
        ADD KEY `idx_schedule` (`schedule_id`)
    ");

    if (!tableExists($pdo, 'quiz_submissions')) {
        $pdo->exec("
            CREATE TABLE `quiz_submissions` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `student_id` INT NOT NULL,
                `quiz_id` INT NOT NULL,
                `class_id` INT NOT NULL,
                `score` DECIMAL(5,2) DEFAULT NULL,
                `status` VARCHAR(50) DEFAULT 'completed',
                `answers_json` LONGTEXT,
                `feedback` TEXT DEFAULT NULL,
                `rubric_data` LONGTEXT DEFAULT NULL,
                `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_submission` (`student_id`, `quiz_id`, `class_id`),
                KEY `idx_student` (`student_id`),
                KEY `idx_quiz` (`quiz_id`),
                KEY `idx_class` (`class_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    } else {
        executeIfMissing($pdo, !columnExists($pdo, 'quiz_submissions', 'class_id'), "
            ALTER TABLE `quiz_submissions`
            ADD COLUMN `class_id` INT NULL AFTER `quiz_id`
        ");
        executeIfMissing($pdo, !columnExists($pdo, 'quiz_submissions', 'feedback'), "
            ALTER TABLE `quiz_submissions`
            ADD COLUMN `feedback` TEXT NULL AFTER `answers_json`
        ");
        executeIfMissing($pdo, !columnExists($pdo, 'quiz_submissions', 'rubric_data'), "
            ALTER TABLE `quiz_submissions`
            ADD COLUMN `rubric_data` LONGTEXT NULL AFTER `feedback`
        ");
        executeIfMissing($pdo, !indexExists($pdo, 'quiz_submissions', 'idx_class'), "
            ALTER TABLE `quiz_submissions`
            ADD KEY `idx_class` (`class_id`)
        ");

        $pdo->exec("
            UPDATE `quiz_submissions` qs
            JOIN (
                SELECT submission_id, class_id
                FROM (
                    SELECT
                        qs2.id AS submission_id,
                        e.class_id,
                        ROW_NUMBER() OVER (
                            PARTITION BY qs2.id
                            ORDER BY
                                CASE WHEN e.status = 'active' THEN 0 ELSE 1 END,
                                e.enrollment_date DESC,
                                e.id DESC
                        ) AS row_num
                    FROM `quiz_submissions` qs2
                    JOIN `quizzes` q ON q.id = qs2.quiz_id
                    JOIN `lessons` l ON l.id = q.lesson_id
                    JOIN `enrollments` e ON e.student_id = qs2.student_id
                    JOIN `classes` c ON c.id = e.class_id AND c.course_id = l.course_id
                    WHERE qs2.class_id IS NULL
                ) ranked
                WHERE row_num = 1
            ) resolved ON resolved.submission_id = qs.id
            SET qs.class_id = resolved.class_id
            WHERE qs.class_id IS NULL
        ");

        if (indexExists($pdo, 'quiz_submissions', 'unique_submission')) {
            $pdo->exec("ALTER TABLE `quiz_submissions` DROP INDEX `unique_submission`");
        }
        $pdo->exec("
            ALTER TABLE `quiz_submissions`
            ADD UNIQUE KEY `unique_submission` (`student_id`, `quiz_id`, `class_id`)
        ");

        $nullClassCount = (int) $pdo->query("SELECT COUNT(*) FROM `quiz_submissions` WHERE `class_id` IS NULL")->fetchColumn();
        if ($nullClassCount === 0) {
            $pdo->exec("
                ALTER TABLE `quiz_submissions`
                MODIFY COLUMN `class_id` INT NOT NULL
            ");
        }
    }

    if (!foreignKeyExists($pdo, 'academic_warnings', 'fk_academic_warnings_class')) {
        $pdo->exec("
            ALTER TABLE `academic_warnings`
            ADD CONSTRAINT `fk_academic_warnings_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
            ADD CONSTRAINT `fk_academic_warnings_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
            ADD CONSTRAINT `fk_academic_warnings_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ");
    }

    if (!foreignKeyExists($pdo, 'attendance_records', 'fk_attendance_records_schedule')) {
        $pdo->exec("
            ALTER TABLE `attendance_records`
            ADD CONSTRAINT `fk_attendance_records_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
            ADD CONSTRAINT `fk_attendance_records_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ");
    }

    if (!foreignKeyExists($pdo, 'leave_requests', 'fk_leave_requests_class')) {
        $pdo->exec("
            ALTER TABLE `leave_requests`
            ADD CONSTRAINT `fk_leave_requests_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
            ADD CONSTRAINT `fk_leave_requests_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ");
    }

    if (!foreignKeyExists($pdo, 'course_materials', 'fk_course_materials_course')) {
        $pdo->exec("
            ALTER TABLE `course_materials`
            ADD CONSTRAINT `fk_course_materials_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
            ADD CONSTRAINT `fk_course_materials_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL,
            ADD CONSTRAINT `fk_course_materials_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL,
            ADD CONSTRAINT `fk_course_materials_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ");
    }

    if (!foreignKeyExists($pdo, 'quiz_submissions', 'fk_quiz_submissions_class')) {
        $pdo->exec("
            ALTER TABLE `quiz_submissions`
            ADD CONSTRAINT `fk_quiz_submissions_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
            ADD CONSTRAINT `fk_quiz_submissions_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
            ADD CONSTRAINT `fk_quiz_submissions_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ");
    }

    $pdo->prepare("
        UPDATE `users`
        SET `password` = ?
        WHERE `password` = '123456'
    ")->execute(['$2y$12$WbMCTCIl7Ghavcv.XdUPi.gN/tj6PMqEc3pTQzSQoNoVHhOM571A2']);

    $pdo->commit();
    echo "Migration completed successfully.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Migration failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
