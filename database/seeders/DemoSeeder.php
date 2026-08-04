<?php

namespace Database\Seeders;

use App\Models\EnrollmentRequest;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherApproval;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $password = 'Demo123!';

        $subjectPool = [
            'Mathematics' => ['code' => 'MATH', 'room' => '301'],
            'Science' => ['code' => 'SCI', 'room' => '302'],
            'English' => ['code' => 'ENG', 'room' => '303'],
            'Filipino' => ['code' => 'FIL', 'room' => '304'],
            'Araling Panlipunan' => ['code' => 'AP', 'room' => '305'],
            'MAPEH' => ['code' => 'MAPEH', 'room' => '306'],
            'TLE' => ['code' => 'TLE', 'room' => '307'],
            'ESP' => ['code' => 'ESP', 'room' => '308'],
        ];

        $teachers = [
            ['name' => 'Maria Santos', 'username' => 'maria.santos', 'advisory' => '7-A', 'subjects' => ['Mathematics', 'Science', 'English', 'Filipino', 'MAPEH']],
            ['name' => 'John Cruz', 'username' => 'john.cruz', 'advisory' => '7-B', 'subjects' => ['Mathematics', 'Science', 'English', 'Araling Panlipunan', 'TLE']],
            ['name' => 'Elena Reyes', 'username' => 'elena.reyes', 'advisory' => '7-C', 'subjects' => ['Mathematics', 'Science', 'English', 'Filipino', 'ESP']],
            ['name' => 'Rizalina Bautista', 'username' => 'rizalina.bautista', 'advisory' => '8-A', 'subjects' => ['Mathematics', 'Science', 'English', 'Araling Panlipunan', 'TLE']],
        ];

        $classes = [
            '7-A' => 7,
            '7-B' => 7,
            '7-C' => 7,
            '8-A' => 8,
        ];

        $studentNames = [
            '7-A' => ['Juan Dela Cruz', 'Maria Clara', 'Andres Bonifacio'],
            '7-B' => ['Jose Rizal', 'Melchora Aquino', 'Emilio Aguinaldo'],
            '7-C' => ['Gregorio Del Pilar', 'Josefa Llanes', 'Apolinario Mabini'],
            '8-A' => ['Gabriela Silang', 'Lapu-Lapu'],
        ];

        $teacherIds = [];

        foreach ($teachers as $i => $spec) {
            $user = User::create([
                'name' => $spec['name'],
                'username' => $spec['username'],
                'email' => $spec['username'] . '@dmnhs.edu',
                'password_hash' => Hash::make($password),
                'role' => 'teacher',
                'status' => 'active',
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'advisory_class' => $spec['advisory'],
                'max_students' => 40,
                'max_subjects' => 8,
                'status' => 'active',
            ]);

            TeacherApproval::create([
                'teacher_id' => $teacher->id,
                'max_students' => 40,
                'max_subjects' => 8,
                'status' => 'approved',
            ]);

            foreach ($spec['subjects'] as $name) {
                TeacherSubject::create([
                    'teacher_id' => $teacher->id,
                    'subject_name' => $name,
                    'course_code' => $subjectPool[$name]['code'],
                    'teacher_code' => 'T-' . ($i + 1),
                    'room_no' => $subjectPool[$name]['room'],
                ]);
            }

            $teacherIds[$spec['advisory']] = $teacher->id;
        }

        $studentCount = 0;
        foreach ($classes as $advisory => $gradeLevel) {
            foreach ($studentNames[$advisory] as $name) {
                $studentCount++;
                $username = strtolower(str_replace(' ', '.', $name));

                $user = User::create([
                    'name' => $name,
                    'username' => $username,
                    'email' => $username . '@dmnhs.edu',
                    'password_hash' => Hash::make($password),
                    'role' => 'student',
                    'status' => 'active',
                ]);

                $student = Student::create([
                    'user_id' => $user->id,
                    'sex' => 'M',
                    'birthday' => now()->subYears($gradeLevel + 10)->toDateString(),
                    'age' => $gradeLevel + 10,
                    'grade_level' => $gradeLevel,
                    'status' => 'active',
                    'needs_reenrollment' => 'no',
                ]);

                $teacherId = $teacherIds[$advisory];

                EnrollmentRequest::create([
                    'student_id' => $student->id,
                    'teacher_id' => $teacherId,
                    'status' => 'approved',
                    'date_requested' => now()->subMonths(4),
                ]);

                $teacherSubjects = TeacherSubject::where('teacher_id', $teacherId)->orderBy('id')->get();
                $subjectIndex = 0;

                foreach ($teacherSubjects as $ts) {
                    $subjectId = DB::table('subjects')->insertGetId([
                        'teacher_id' => $teacherId,
                        'student_id' => $student->id,
                        'subject_name' => $ts->subject_name,
                        'course_code' => $ts->course_code,
                        'teacher_code' => $ts->teacher_code,
                        'room_no' => $ts->room_no,
                        'created_at' => now(),
                    ]);

                    $gradeValue = 80 + (($studentCount + $subjectIndex) % 15);
                    $remarks = $gradeValue >= 90 ? 'Outstanding' : ($gradeValue >= 83 ? 'Satisfactory' : 'Fairly Satisfactory');

                    Grade::create([
                        'student_id' => $student->id,
                        'subject_id' => $subjectId,
                        'grade' => (string) $gradeValue,
                        'remarks' => $remarks,
                        'quarter' => 'Term 1',
                        'date_submitted' => now()->subMonths(1),
                    ]);

                    $subjectIndex++;
                }
            }
        }
    }
}
