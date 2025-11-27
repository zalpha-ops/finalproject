-- Populate Eagle Flight School Database with Fake Data
USE `eagle-flight-school`;

-- Clear existing data (except admin)
DELETE FROM assignment_submissions;
DELETE FROM assignments;
DELETE FROM training_hours;
DELETE FROM schedules;
DELETE FROM grades;
DELETE FROM progress;
DELETE FROM student_instructors;
DELETE FROM course_instructors;
DELETE FROM guardians;
DELETE FROM student_profiles WHERE student_id != 'ADMIN001';
DELETE FROM instructors;
DELETE FROM users WHERE username != 'admin';
DELETE FROM announcements;
DELETE FROM achievements;
DELETE FROM logs;

-- Insert Instructors
INSERT INTO `instructors` (`name`, `email`, `username`, `password`, `phone`, `certifications`, `status`) VALUES
('Captain John Smith', 'john.smith@eagleflight.com', 'john.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0101', 'CFI, CFII, MEI, ATP', 'active'),
('Mary Johnson', 'mary.johnson@eagleflight.com', 'mary.johnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0102', 'CFI, CFII, Commercial Pilot', 'active'),
('Robert Williams', 'robert.williams@eagleflight.com', 'robert.williams', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0103', 'CFI, MEI, ATP', 'active'),
('Sarah Davis', 'sarah.davis@eagleflight.com', 'sarah.davis', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0104', 'CFI, CFII', 'active'),
('Michael Brown', 'michael.brown@eagleflight.com', 'michael.brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0105', 'CFI, Commercial Pilot', 'active');

-- Insert Student Users
INSERT INTO `users` (`username`, `password`, `email`, `role`) VALUES
('james.wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'james.wilson@email.com', 'student'),
('emma.taylor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'emma.taylor@email.com', 'student'),
('oliver.anderson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'oliver.anderson@email.com', 'student'),
('sophia.thomas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sophia.thomas@email.com', 'student'),
('liam.jackson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'liam.jackson@email.com', 'student'),
('ava.white', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ava.white@email.com', 'student'),
('noah.harris', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'noah.harris@email.com', 'student'),
('isabella.martin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'isabella.martin@email.com', 'student'),
('ethan.thompson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ethan.thompson@email.com', 'student'),
('mia.garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mia.garcia@email.com', 'student');

-- Insert Student Profiles
INSERT INTO `student_profiles` (`user_id`, `student_id`, `name`, `email`, `phone`, `date_of_birth`, `address`, `emergency_contact`, `emergency_phone`, `medical_certificate`, `enrollment_date`, `status`) VALUES
(2, 'STU001', 'James Wilson', 'james.wilson@email.com', '555-1001', '1998-03-15', '123 Aviation Blvd, Sky City', 'Linda Wilson', '555-1002', 'Class 1', '2024-01-15', 'active'),
(3, 'STU002', 'Emma Taylor', 'emma.taylor@email.com', '555-1003', '1999-07-22', '456 Pilot Lane, Sky City', 'David Taylor', '555-1004', 'Class 2', '2024-02-01', 'active'),
(4, 'STU003', 'Oliver Anderson', 'oliver.anderson@email.com', '555-1005', '1997-11-08', '789 Flight St, Sky City', 'Patricia Anderson', '555-1006', 'Class 1', '2024-01-20', 'active'),
(5, 'STU004', 'Sophia Thomas', 'sophia.thomas@email.com', '555-1007', '2000-05-30', '321 Runway Ave, Sky City', 'Mark Thomas', '555-1008', 'Class 2', '2024-03-10', 'active'),
(6, 'STU005', 'Liam Jackson', 'liam.jackson@email.com', '555-1009', '1998-09-12', '654 Hangar Rd, Sky City', 'Jennifer Jackson', '555-1010', 'Class 1', '2024-02-15', 'active'),
(7, 'STU006', 'Ava White', 'ava.white@email.com', '555-1011', '1999-12-25', '987 Airfield Dr, Sky City', 'Robert White', '555-1012', 'Class 2', '2024-03-01', 'active'),
(8, 'STU007', 'Noah Harris', 'noah.harris@email.com', '555-1013', '1997-04-18', '147 Terminal Way, Sky City', 'Susan Harris', '555-1014', 'Class 1', '2024-01-25', 'active'),
(9, 'STU008', 'Isabella Martin', 'isabella.martin@email.com', '555-1015', '2000-08-07', '258 Control Tower Ln, Sky City', 'James Martin', '555-1016', 'Class 2', '2024-02-20', 'active'),
(10, 'STU009', 'Ethan Thompson', 'ethan.thompson@email.com', '555-1017', '1998-01-14', '369 Cockpit Ct, Sky City', 'Mary Thompson', '555-1018', 'Class 1', '2024-03-05', 'active'),
(11, 'STU010', 'Mia Garcia', 'mia.garcia@email.com', '555-1019', '1999-06-29', '741 Propeller Pl, Sky City', 'Carlos Garcia', '555-1020', 'Class 2', '2024-01-30', 'active');

-- Assign Instructors to Students
INSERT INTO `student_instructors` (`student_id`, `instructor_id`) VALUES
(1, 1), (2, 1), (3, 2), (4, 2), (5, 3),
(6, 3), (7, 4), (8, 4), (9, 5), (10, 5);

-- Assign Instructors to Courses
INSERT INTO `course_instructors` (`course_id`, `instructor_id`) VALUES
(1, 1), (1, 2), (2, 1), (2, 3), (3, 2), (3, 4);

-- Insert Grades
INSERT INTO `grades` (`student_id`, `course_id`, `grade`, `score`, `instructor_id`, `comments`) VALUES
(1, 1, 'A', 92.50, 1, 'Excellent progress in flight training'),
(2, 1, 'B+', 87.00, 1, 'Good understanding of aviation principles'),
(3, 1, 'A-', 90.00, 2, 'Strong performance in ground school'),
(4, 1, 'B', 85.50, 2, 'Needs more practice with crosswind landings'),
(5, 2, 'A', 94.00, 3, 'Outstanding commercial pilot skills'),
(6, 3, 'B+', 88.50, 4, 'Good instrument flying techniques'),
(7, 1, 'A-', 91.00, 4, 'Excellent solo flight performance'),
(8, 1, 'B', 84.00, 4, 'Improving steadily'),
(9, 2, 'A', 93.50, 5, 'Exceptional navigation skills'),
(10, 3, 'B+', 89.00, 5, 'Strong IFR procedures');

-- Insert Progress
INSERT INTO `progress` (`student_id`, `course_id`, `completion_percentage`) VALUES
(1, 1, 75.50), (2, 1, 65.00), (3, 1, 80.25), (4, 1, 55.75),
(5, 2, 45.00), (6, 3, 60.50), (7, 1, 70.00), (8, 1, 50.25),
(9, 2, 55.75), (10, 3, 68.00);

-- Insert Assignments
INSERT INTO `assignments` (`course_id`, `title`, `description`, `due_date`, `max_points`) VALUES
(1, 'Pre-Flight Checklist Report', 'Complete a detailed pre-flight checklist and submit a report', '2024-12-15 23:59:59', 100),
(1, 'Weather Analysis Assignment', 'Analyze weather patterns and their impact on flight safety', '2024-12-20 23:59:59', 100),
(2, 'Cross-Country Flight Planning', 'Plan a cross-country flight with multiple waypoints', '2024-12-18 23:59:59', 150),
(3, 'IFR Approach Procedures', 'Document and explain various IFR approach procedures', '2024-12-22 23:59:59', 100);

-- Insert Assignment Submissions
INSERT INTO `assignment_submissions` (`assignment_id`, `student_id`, `submission_text`, `grade`, `feedback`, `graded_by`) VALUES
(1, 1, 'Completed comprehensive pre-flight checklist covering all aircraft systems...', 95.00, 'Excellent attention to detail', 1),
(1, 2, 'Pre-flight checklist submitted with detailed notes...', 88.00, 'Good work, minor improvements needed', 1),
(2, 3, 'Weather analysis report covering METAR and TAF interpretation...', 92.00, 'Strong understanding of weather systems', 2),
(3, 5, 'Cross-country flight plan from KSFO to KLAX with fuel calculations...', 96.00, 'Outstanding flight planning', 3);

-- Insert Schedules
INSERT INTO `schedules` (`student_id`, `instructor_id`, `course_id`, `session_date`, `start_time`, `end_time`, `session_type`, `aircraft_registration`, `location`, `status`, `notes`) VALUES
(1, 1, 1, '2024-11-28', '09:00:00', '11:00:00', 'flight', 'N12345', 'Sky Harbor Airport', 'scheduled', 'Pattern work and touch-and-goes'),
(2, 1, 1, '2024-11-28', '13:00:00', '15:00:00', 'ground', NULL, 'Classroom A', 'scheduled', 'Aviation regulations review'),
(3, 2, 1, '2024-11-29', '10:00:00', '12:00:00', 'flight', 'N67890', 'Sky Harbor Airport', 'scheduled', 'Solo flight practice'),
(4, 2, 1, '2024-11-29', '14:00:00', '16:00:00', 'simulator', NULL, 'Simulator Room 1', 'scheduled', 'Emergency procedures training'),
(5, 3, 2, '2024-11-30', '08:00:00', '10:00:00', 'flight', 'N12345', 'Sky Harbor Airport', 'scheduled', 'Commercial maneuvers'),
(6, 4, 3, '2024-11-30', '11:00:00', '13:00:00', 'flight', 'N11111', 'Sky Harbor Airport', 'scheduled', 'IFR approach practice'),
(7, 4, 1, '2024-12-01', '09:00:00', '11:00:00', 'flight', 'N67890', 'Sky Harbor Airport', 'scheduled', 'Cross-country navigation'),
(1, 1, 1, '2024-11-25', '10:00:00', '12:00:00', 'flight', 'N12345', 'Sky Harbor Airport', 'completed', 'Completed pattern work'),
(2, 1, 1, '2024-11-26', '14:00:00', '16:00:00', 'ground', NULL, 'Classroom A', 'completed', 'Completed ground school session');

-- Insert Training Hours
INSERT INTO `training_hours` (`student_id`, `instructor_id`, `aircraft_registration`, `flight_date`, `duration_hours`, `flight_type`, `notes`) VALUES
(1, 1, 'N12345', '2024-11-20', 2.5, 'dual', 'Pattern work and landings'),
(1, 1, 'N12345', '2024-11-22', 1.8, 'solo', 'First solo flight - excellent performance'),
(2, 1, 'N67890', '2024-11-21', 2.0, 'dual', 'Stall recovery practice'),
(3, 2, 'N12345', '2024-11-23', 3.2, 'cross-country', 'Cross-country to nearby airport'),
(5, 3, 'N67890', '2024-11-24', 2.7, 'dual', 'Commercial maneuvers practice'),
(6, 4, 'N11111', '2024-11-25', 1.5, 'dual', 'Instrument approach procedures'),
(7, 4, 'N12345', '2024-11-26', 2.3, 'dual', 'Night flying introduction'),
(9, 5, 'N67890', '2024-11-27', 2.9, 'cross-country', 'Long cross-country flight');

-- Insert Announcements
INSERT INTO `announcements` (`title`, `content`, `author_id`, `target_audience`, `priority`, `expires_at`) VALUES
('Welcome to Eagle Flight School!', 'We are excited to have you join our aviation family. Check your schedule regularly and stay safe in the skies!', 1, 'all', 'high', '2025-01-31 23:59:59'),
('Weather Advisory', 'Strong winds expected this weekend. All flights will be evaluated on a case-by-case basis. Contact your instructor.', 1, 'all', 'high', '2024-12-05 23:59:59'),
('New Simulator Available', 'Our new advanced flight simulator is now available for booking. Contact the front desk to schedule your session.', 1, 'students', 'normal', '2024-12-31 23:59:59'),
('Instructor Meeting', 'Monthly instructor meeting scheduled for December 5th at 6 PM in Conference Room B.', 1, 'instructors', 'normal', '2024-12-05 23:59:59'),
('Holiday Schedule', 'The school will be closed December 24-26 for the holidays. Regular operations resume December 27th.', 1, 'all', 'high', '2024-12-27 23:59:59');

-- Insert Achievements
INSERT INTO `achievements` (`student_id`, `achievement_type`, `title`, `description`, `earned_date`) VALUES
(1, 'milestone', 'First Solo Flight', 'Successfully completed first solo flight', '2024-11-22'),
(3, 'milestone', 'Cross-Country Solo', 'Completed first solo cross-country flight', '2024-11-23'),
(5, 'certification', 'Private Pilot License', 'Earned Private Pilot License certification', '2024-11-15'),
(1, 'achievement', 'Perfect Landing', 'Achieved perfect landing score during evaluation', '2024-11-20'),
(7, 'milestone', 'Night Flight Qualified', 'Completed night flying requirements', '2024-11-26');

-- Insert Activity Logs
INSERT INTO `logs` (`user_id`, `action`, `description`, `ip_address`) VALUES
(1, 'login', 'Admin logged in', '127.0.0.1'),
(2, 'login', 'Student logged in', '127.0.0.1'),
(2, 'view_grades', 'Viewed course grades', '127.0.0.1'),
(3, 'submit_assignment', 'Submitted assignment: Weather Analysis', '127.0.0.1'),
(1, 'add_announcement', 'Created new announcement', '127.0.0.1'),
(2, 'view_schedule', 'Checked flight schedule', '127.0.0.1');

SELECT 'Fake data populated successfully!' AS Status;
SELECT COUNT(*) AS 'Total Students' FROM student_profiles;
SELECT COUNT(*) AS 'Total Instructors' FROM instructors;
SELECT COUNT(*) AS 'Total Courses' FROM courses;
SELECT COUNT(*) AS 'Total Schedules' FROM schedules;
