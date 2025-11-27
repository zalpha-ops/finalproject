<?php
session_start();
require 'db_connect.php';
require 'auth.php';
require_role('admin');

// Get all students, instructors, courses, and aircraft for dropdowns
$students = $pdo->query("SELECT id, name, student_id FROM student_profiles ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$instructors = $pdo->query("SELECT instructor_id, name FROM instructors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT id, title FROM courses ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
$aircraft = $pdo->query("SELECT registration, model FROM aircraft WHERE status = 'available' ORDER BY registration")->fetchAll(PDO::FETCH_ASSOC);

// Get all schedules
$schedules = $pdo->query("
    SELECT s.*, 
           sp.name as student_name,
           sp.student_id as student_number,
           i.name as instructor_name,
           c.title as course_title
    FROM schedules s
    JOIN student_profiles sp ON s.student_id = sp.id
    JOIN instructors i ON s.instructor_id = i.instructor_id
    LEFT JOIN courses c ON s.course_id = c.id
    ORDER BY s.session_date DESC, s.start_time DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules - Eagle Flight School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-purple: #6B46C1;
            --dark-purple: #4C1D95;
        }
        
        body {
            background: linear-gradient(135deg, #0F0F0F 0%, #4C1D95 50%, #6B46C1 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(107, 70, 193, 0.4);
        }
        
        .btn-purple {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--dark-purple) 100%);
            border: none;
            color: white;
        }
        
        .btn-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(107, 70, 193, 0.4);
            color: white;
        }
        
        .schedule-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .type-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-flight { background: #0ea5e9; color: white; }
        .badge-ground { background: #10b981; color: white; }
        .badge-simulator { background: #f59e0b; color: white; }
        .badge-exam { background: #ec4899; color: white; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>📅 Manage Schedules</h2>
                <p class="mb-0">Create and manage flight training schedules</p>
            </div>
            <div>
                <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                    ➕ Create Schedule
                </button>
                <a href="admin_dashboard.php" class="btn btn-outline-light">← Back</a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Schedules List -->
    <div class="schedule-card">
        <h4 class="mb-4">All Schedules</h4>
        
        <?php if (empty($schedules)): ?>
            <div class="alert alert-info">
                No schedules found. Click "Create Schedule" to add one.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Student</th>
                            <th>Instructor</th>
                            <th>Type</th>
                            <th>Aircraft</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($schedule['session_date'])) ?></td>
                                <td><?= date('g:i A', strtotime($schedule['start_time'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($schedule['student_name']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($schedule['student_number']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($schedule['instructor_name']) ?></td>
                                <td>
                                    <span class="type-badge badge-<?= $schedule['session_type'] ?>">
                                        <?= ucfirst($schedule['session_type']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($schedule['aircraft_registration'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= $schedule['status'] === 'completed' ? 'success' : ($schedule['status'] === 'cancelled' ? 'danger' : 'primary') ?>">
                                        <?= ucfirst($schedule['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editSchedule(<?= $schedule['id'] ?>)">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule(<?= $schedule['id'] ?>)">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Schedule Modal -->
<div class="modal fade" id="createScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createScheduleForm" method="POST" action="process_schedule.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student *</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>">
                                        <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['student_id']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Instructor *</label>
                            <select name="instructor_id" class="form-select" required>
                                <option value="">Select Instructor</option>
                                <?php foreach ($instructors as $instructor): ?>
                                    <option value="<?= $instructor['instructor_id'] ?>">
                                        <?= htmlspecialchars($instructor['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Session Type *</label>
                            <select name="session_type" class="form-select" required onchange="toggleAircraft(this)">
                                <option value="">Select Type</option>
                                <option value="flight">✈️ Flight Training</option>
                                <option value="ground">📚 Ground School</option>
                                <option value="simulator">🎮 Simulator</option>
                                <option value="exam">📝 Exam</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Course (Optional)</label>
                            <select name="course_id" class="form-select">
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['course_id'] ?>">
                                        <?= htmlspecialchars($course['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="session_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start Time *</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">End Time *</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6 mb-3" id="aircraftField" style="display:none;">
                            <label class="form-label">Aircraft</label>
                            <select name="aircraft_registration" class="form-select">
                                <option value="">Select Aircraft</option>
                                <?php foreach ($aircraft as $plane): ?>
                                    <option value="<?= $plane['registration'] ?>">
                                        <?= htmlspecialchars($plane['registration']) ?> - <?= htmlspecialchars($plane['model']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location *</label>
                            <input type="text" name="location" class="form-control" required placeholder="e.g., Hangar A, Classroom 1">
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional information..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-purple">Create Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal" id="editScheduleModal" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Schedule</h5>
                <button type="button" class="btn-close" onclick="closeEditModal()"></button>
            </div>
            <form id="editScheduleForm" method="POST" action="process_schedule.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="editScheduleId" name="schedule_id">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student *</label>
                            <select name="student_id" id="editStudentId" class="form-select" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>">
                                        <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['student_id']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Instructor *</label>
                            <select name="instructor_id" id="editInstructorId" class="form-select" required>
                                <option value="">Select Instructor</option>
                                <?php foreach ($instructors as $instructor): ?>
                                    <option value="<?= $instructor['instructor_id'] ?>">
                                        <?= htmlspecialchars($instructor['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Session Type *</label>
                            <select name="session_type" id="editSessionType" class="form-select" required onchange="toggleEditAircraft(this)">
                                <option value="">Select Type</option>
                                <option value="flight">✈️ Flight Training</option>
                                <option value="ground">📚 Ground School</option>
                                <option value="simulator">🎮 Simulator</option>
                                <option value="exam">📝 Exam</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Course (Optional)</label>
                            <select name="course_id" id="editCourseId" class="form-select">
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['course_id'] ?>">
                                        <?= htmlspecialchars($course['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" name="session_date" id="editSessionDate" class="form-control" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start Time *</label>
                            <input type="time" name="start_time" id="editStartTime" class="form-control" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">End Time *</label>
                            <input type="time" name="end_time" id="editEndTime" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6 mb-3" id="editAircraftField" style="display:none;">
                            <label class="form-label">Aircraft</label>
                            <select name="aircraft_registration" id="editAircraftReg" class="form-select">
                                <option value="">Select Aircraft</option>
                                <?php foreach ($aircraft as $plane): ?>
                                    <option value="<?= $plane['registration'] ?>">
                                        <?= htmlspecialchars($plane['registration']) ?> - <?= htmlspecialchars($plane['model']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location *</label>
                            <input type="text" name="location" id="editLocation" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="editStatus" class="form-select">
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="rescheduled">Rescheduled</option>
                            </select>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="editNotes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-purple">Update Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleAircraft(select) {
    const aircraftField = document.getElementById('aircraftField');
    if (select.value === 'flight') {
        aircraftField.style.display = 'block';
    } else {
        aircraftField.style.display = 'none';
    }
}

function deleteSchedule(id) {
    if (confirm('Are you sure you want to delete this schedule?')) {
        window.location.href = 'process_schedule.php?action=delete&id=' + id;
    }
}

function editSchedule(id) {
    console.log('Editing schedule ID:', id);
    
    // Find the schedule data
    fetch('get_schedule.php?id=' + id)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                populateEditForm(data.schedule);
                document.getElementById('editScheduleModal').style.display = 'block';
            } else {
                alert('Error loading schedule: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            alert('Error loading schedule data: ' + err.message);
        });
}

function populateEditForm(schedule) {
    document.getElementById('editScheduleId').value = schedule.id;
    document.getElementById('editStudentId').value = schedule.student_id;
    document.getElementById('editInstructorId').value = schedule.instructor_id;
    document.getElementById('editSessionType').value = schedule.session_type;
    document.getElementById('editCourseId').value = schedule.course_id || '';
    document.getElementById('editSessionDate').value = schedule.session_date;
    document.getElementById('editStartTime').value = schedule.start_time;
    document.getElementById('editEndTime').value = schedule.end_time;
    document.getElementById('editAircraftReg').value = schedule.aircraft_registration || '';
    document.getElementById('editLocation').value = schedule.location;
    document.getElementById('editStatus').value = schedule.status;
    document.getElementById('editNotes').value = schedule.notes || '';
    
    // Show aircraft field if flight type
    if (schedule.session_type === 'flight') {
        document.getElementById('editAircraftField').style.display = 'block';
    } else {
        document.getElementById('editAircraftField').style.display = 'none';
    }
}

function closeEditModal() {
    document.getElementById('editScheduleModal').style.display = 'none';
}

function toggleEditAircraft(select) {
    const aircraftField = document.getElementById('editAircraftField');
    if (select.value === 'flight') {
        aircraftField.style.display = 'block';
    } else {
        aircraftField.style.display = 'none';
    }
}
</script>
</body>
</html>
