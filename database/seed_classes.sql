-- ══════════════════════════════════════════════════════════════
-- Dữ liệu mẫu: Ghi danh học viên vào các lớp của Teacher (id=3)
-- ══════════════════════════════════════════════════════════════

-- Lớp 7: Giao tiếp Cơ bản - Sáng 246 (course_id=1)
INSERT INTO enrollments (student_id, class_id, class_detail_id, status, enrollment_date) VALUES
(1, 7, NULL, 'active', '2026-04-01 08:00:00'),
(4, 7, NULL, 'active', '2026-04-01 08:00:00'),
(7, 7, NULL, 'active', '2026-04-01 08:00:00'),
(9, 7, NULL, 'active', '2026-04-02 08:00:00'),
(10, 7, NULL, 'active', '2026-04-02 08:00:00'),
(11, 7, NULL, 'active', '2026-04-03 08:00:00'),
(12, 7, NULL, 'active', '2026-04-03 08:00:00'),
(13, 7, NULL, 'active', '2026-04-04 08:00:00');

-- Lớp 8: English for Office - Tối 246 (course_id=2)
INSERT INTO enrollments (student_id, class_id, class_detail_id, status, enrollment_date) VALUES
(1, 8, NULL, 'active', '2026-04-01 08:00:00'),
(14, 8, NULL, 'active', '2026-04-01 08:00:00'),
(15, 8, NULL, 'active', '2026-04-02 08:00:00'),
(16, 8, NULL, 'active', '2026-04-02 08:00:00'),
(17, 8, NULL, 'active', '2026-04-03 08:00:00'),
(18, 8, NULL, 'active', '2026-04-03 08:00:00');

-- Lớp 9: TOEIC Cấp tốc - Tối 357 (course_id=3)
INSERT INTO enrollments (student_id, class_id, class_detail_id, status, enrollment_date) VALUES
(1, 9, NULL, 'active', '2026-04-01 08:00:00'),
(7, 9, NULL, 'active', '2026-04-01 08:00:00'),
(8, 9, NULL, 'active', '2026-04-01 08:00:00'),
(10, 9, NULL, 'active', '2026-04-02 08:00:00'),
(11, 9, NULL, 'active', '2026-04-02 08:00:00'),
(14, 9, NULL, 'active', '2026-04-03 08:00:00'),
(15, 9, NULL, 'active', '2026-04-03 08:00:00'),
(16, 9, NULL, 'active', '2026-04-04 08:00:00'),
(19, 9, NULL, 'active', '2026-04-04 08:00:00');

-- Lớp 10: IELTS Masterclass - Sáng 357 (course_id=4)
INSERT INTO enrollments (student_id, class_id, class_detail_id, status, enrollment_date) VALUES
(1, 10, NULL, 'active', '2026-04-01 08:00:00'),
(4, 10, NULL, 'active', '2026-04-01 08:00:00'),
(9, 10, NULL, 'active', '2026-04-02 08:00:00'),
(12, 10, NULL, 'active', '2026-04-02 08:00:00'),
(13, 10, NULL, 'active', '2026-04-03 08:00:00');

-- Lớp 11: Ngữ pháp trọn bộ - Cuối tuần (course_id=5)
INSERT INTO enrollments (student_id, class_id, class_detail_id, status, enrollment_date) VALUES
(7, 11, NULL, 'active', '2026-04-01 08:00:00'),
(8, 11, NULL, 'active', '2026-04-01 08:00:00'),
(9, 11, NULL, 'active', '2026-04-01 08:00:00'),
(10, 11, NULL, 'active', '2026-04-02 08:00:00'),
(11, 11, NULL, 'active', '2026-04-02 08:00:00'),
(12, 11, NULL, 'active', '2026-04-02 08:00:00'),
(13, 11, NULL, 'active', '2026-04-03 08:00:00'),
(14, 11, NULL, 'active', '2026-04-03 08:00:00'),
(15, 11, NULL, 'active', '2026-04-04 08:00:00'),
(16, 11, NULL, 'active', '2026-04-04 08:00:00'),
(17, 11, NULL, 'active', '2026-04-04 08:00:00'),
(18, 11, NULL, 'active', '2026-04-05 08:00:00');

-- ══════════════════════════════════════════════════════════════
-- Dữ liệu mẫu: Điểm danh cho các buổi đã qua
-- ══════════════════════════════════════════════════════════════

-- Lấy các schedule_id đã có (lớp 2 - IELTS 01 đã có dữ liệu)
-- Cần thêm schedules cho các lớp khác trước

-- Lớp 7: 3 buổi học đã qua
INSERT INTO schedules (class_id, lesson_id, teacher_id, study_date, start_time, end_time, room_info, teaching_type, status, attendance_checked)
VALUES
(7, NULL, 3, '2026-04-02', '08:00:00', '09:30:00', 'Phòng 201', 'offline', 'completed', 1),
(7, NULL, 3, '2026-04-04', '08:00:00', '09:30:00', 'Phòng 201', 'offline', 'completed', 1),
(7, NULL, 3, '2026-04-07', '08:00:00', '09:30:00', 'Phòng 201', 'offline', 'completed', 1);

-- Lớp 8: 2 buổi đã qua
INSERT INTO schedules (class_id, lesson_id, teacher_id, study_date, start_time, end_time, room_info, teaching_type, status, attendance_checked)
VALUES
(8, NULL, 3, '2026-04-02', '18:00:00', '19:30:00', 'Phòng 305', 'offline', 'completed', 1),
(8, NULL, 3, '2026-04-04', '18:00:00', '19:30:00', 'Phòng 305', 'offline', 'completed', 1);

-- Lớp 9: 3 buổi đã qua
INSERT INTO schedules (class_id, lesson_id, teacher_id, study_date, start_time, end_time, room_info, teaching_type, status, attendance_checked)
VALUES
(9, NULL, 3, '2026-04-03', '18:00:00', '19:30:00', 'Phòng 102', 'offline', 'completed', 1),
(9, NULL, 3, '2026-04-05', '18:00:00', '19:30:00', 'Phòng 102', 'offline', 'completed', 1),
(9, NULL, 3, '2026-04-08', '18:00:00', '19:30:00', 'Phòng 102', 'offline', 'completed', 1);

-- Lớp 10: 2 buổi đã qua
INSERT INTO schedules (class_id, lesson_id, teacher_id, study_date, start_time, end_time, room_info, teaching_type, status, attendance_checked)
VALUES
(10, NULL, 3, '2026-04-03', '08:00:00', '09:30:00', 'Phòng 401', 'offline', 'completed', 1),
(10, NULL, 3, '2026-04-05', '08:00:00', '09:30:00', 'Phòng 401', 'offline', 'completed', 1);

-- Lớp 11: 2 buổi cuối tuần
INSERT INTO schedules (class_id, lesson_id, teacher_id, study_date, start_time, end_time, room_info, teaching_type, status, attendance_checked)
VALUES
(11, NULL, 3, '2026-04-05', '09:00:00', '11:00:00', 'Phòng Hội trường', 'offline', 'completed', 1),
(11, NULL, 3, '2026-04-12', '09:00:00', '11:00:00', 'Phòng Hội trường', 'offline', 'completed', 1);
