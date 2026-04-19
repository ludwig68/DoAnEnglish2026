-- ══════════════════════════════════════════════════════════════
-- Dữ liệu mẫu: Điểm danh cho các buổi đã qua
-- ══════════════════════════════════════════════════════════════

-- Lớp 7 (schedule 371, 372, 373) - HV: 1,4,7,9,10,11,12,13
INSERT INTO attendance_records (schedule_id, student_id, status, note) VALUES
(371, 1, 'present', NULL), (371, 4, 'present', NULL), (371, 7, 'present', NULL),
(371, 9, 'late', 'Đến trễ 10 phút'), (371, 10, 'present', NULL), (371, 11, 'present', NULL),
(371, 12, 'absent', NULL), (371, 13, 'present', NULL),
(372, 1, 'present', NULL), (372, 4, 'present', NULL), (372, 7, 'absent', 'Nghỉ không phép'),
(372, 9, 'present', NULL), (372, 10, 'present', NULL), (372, 11, 'present', NULL),
(372, 12, 'present', NULL), (372, 13, 'late', NULL),
(373, 1, 'present', NULL), (373, 4, 'present', NULL), (373, 7, 'present', NULL),
(373, 9, 'present', NULL), (373, 10, 'absent', NULL), (373, 11, 'present', NULL),
(373, 12, 'present', NULL), (373, 13, 'present', NULL);

-- Lớp 8 (schedule 374, 375) - HV: 1,14,15,16,17,18
INSERT INTO attendance_records (schedule_id, student_id, status, note) VALUES
(374, 1, 'present', NULL), (374, 14, 'present', NULL), (374, 15, 'present', NULL),
(374, 16, 'late', NULL), (374, 17, 'present', NULL), (374, 18, 'absent', NULL),
(375, 1, 'present', NULL), (375, 14, 'present', NULL), (375, 15, 'present', NULL),
(375, 16, 'present', NULL), (375, 17, 'absent', NULL), (375, 18, 'present', NULL);

-- Lớp 9 (schedule 376, 377, 378) - HV: 1,7,8,10,11,14,15,16,19
INSERT INTO attendance_records (schedule_id, student_id, status, note) VALUES
(376, 1, 'present', NULL), (376, 7, 'present', NULL), (376, 8, 'present', NULL),
(376, 10, 'present', NULL), (376, 11, 'absent', NULL), (376, 14, 'present', NULL),
(376, 15, 'present', NULL), (376, 16, 'present', NULL), (376, 19, 'late', NULL),
(377, 1, 'present', NULL), (377, 7, 'present', NULL), (377, 8, 'late', NULL),
(377, 10, 'present', NULL), (377, 11, 'present', NULL), (377, 14, 'present', NULL),
(377, 15, 'absent', NULL), (377, 16, 'present', NULL), (377, 19, 'present', NULL),
(378, 1, 'present', NULL), (378, 7, 'present', NULL), (378, 8, 'present', NULL),
(378, 10, 'present', NULL), (378, 11, 'present', NULL), (378, 14, 'absent', NULL),
(378, 15, 'present', NULL), (378, 16, 'present', NULL), (378, 19, 'present', NULL);

-- Lớp 10 (schedule 379, 380) - HV: 1,4,9,12,13
INSERT INTO attendance_records (schedule_id, student_id, status, note) VALUES
(379, 1, 'present', NULL), (379, 4, 'present', NULL), (379, 9, 'present', NULL),
(379, 12, 'late', NULL), (379, 13, 'present', NULL),
(380, 1, 'present', NULL), (380, 4, 'absent', NULL), (380, 9, 'present', NULL),
(380, 12, 'present', NULL), (380, 13, 'present', NULL);

-- Lớp 11 (schedule 381, 382) - HV: 7,8,9,10,11,12,13,14,15,16,17,18
INSERT INTO attendance_records (schedule_id, student_id, status, note) VALUES
(381, 7, 'present', NULL), (381, 8, 'present', NULL), (381, 9, 'present', NULL),
(381, 10, 'present', NULL), (381, 11, 'late', NULL), (381, 12, 'present', NULL),
(381, 13, 'present', NULL), (381, 14, 'absent', NULL), (381, 15, 'present', NULL),
(381, 16, 'present', NULL), (381, 17, 'present', NULL), (381, 18, 'present', NULL),
(382, 7, 'present', NULL), (382, 8, 'absent', NULL), (382, 9, 'present', NULL),
(382, 10, 'present', NULL), (382, 11, 'present', NULL), (382, 12, 'present', NULL),
(382, 13, 'present', NULL), (382, 14, 'present', NULL), (382, 15, 'present', NULL),
(382, 16, 'late', NULL), (382, 17, 'present', NULL), (382, 18, 'present', NULL);
