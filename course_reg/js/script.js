
const currentStudent = {
    student_id: 441500338,
    student_name: "Soha Samy Hlway",
    student_email: "soha@yic.edu",
    student_major: "SC",
    current_level: 3
};

const studyPlanCourses = [
    // Level 1
    { code: "CS101", name: "Computer Programming", credits: 3, level: 1, status: "completed", grade: "A", time: "Sun/Tue 10:00", prerequisite: null },
    { code: "MATH101", name: "Calculus I", credits: 4, level: 1, status: "completed", grade: "B+", time: "Sun/Tue 8:00", prerequisite: null },
    { code: "PHYS101", name: "General Physics I", credits: 4, level: 1, status: "completed", grade: "A-", time: "Mon/Wed 9:00", prerequisite: null },
    { code: "ENGL101", name: "English Composition I", credits: 3, level: 1, status: "completed", grade: "B", time: "Wed/Thu 11:00", prerequisite: null },
    { code: "ISLM101", name: "Islamic Ideology", credits: 2, level: 1, status: "completed", grade: "A", time: "Tue/Thu 10:00", prerequisite: null },
    { code: "PE101", name: "Physical Education I", credits: 1, level: 1, status: "completed", grade: "A+", time: "Mon 2:00", prerequisite: null },
    
    // Level 2
    { code: "CS102", name: "Object Oriented Programming", credits: 4, level: 2, status: "completed", grade: "A-", time: "Mon/Wed 1:00", prerequisite: "CS101" },
    { code: "MATH102", name: "Calculus II", credits: 4, level: 2, status: "completed", grade: "B+", time: "Tue/Thu 8:00", prerequisite: "MATH101" },
    { code: "PHYS102", name: "General Physics II", credits: 4, level: 2, status: "completed", grade: "B", time: "Mon/Wed 11:00", prerequisite: "PHYS101" },
    { code: "ENGL131", name: "Academic Writing", credits: 3, level: 2, status: "completed", grade: "A", time: "Sun/Tue 1:00", prerequisite: "ENGL101" },
    { code: "ARAB101", name: "Functional Grammar", credits: 2, level: 2, status: "completed", grade: "B+", time: "Thu 10:00", prerequisite: null },
    { code: "PE102", name: "Physical Education II", credits: 1, level: 2, status: "completed", grade: "A", time: "Wed 2:00", prerequisite: "PE101" },
    
    // Level 3
    { code: "CS204", name: "Data Structures", credits: 4, level: 3, status: "in-progress", grade: null, time: "Sun/Tue 11:30", prerequisite: "CS102" },
    { code: "CS202", name: "Discrete Mathematics", credits: 4, level: 3, status: "in-progress", grade: null, time: "Mon/Wed 2:30", prerequisite: "MATH102" },
    { code: "CS201", name: "Digital Logic", credits: 4, level: 3, status: "completed", grade: "B", time: "Tue/Thu 1:00", prerequisite: null },
    { code: "ARAB201", name: "Objective Writing", credits: 2, level: 3, status: "in-progress", grade: null, time: "Sun 10:00", prerequisite: "ARAB101" },
    
    // Level 4
    { code: "CS301", name: "Computer Architecture", credits: 3, level: 4, status: "remaining", grade: null, time: "TBA", prerequisite: "CS204" },
    { code: "CS302", name: "Algorithms", credits: 3, level: 4, status: "remaining", grade: null, time: "TBA", prerequisite: "CS204" },
    { code: "CS311", name: "Database Systems", credits: 4, level: 4, status: "remaining", grade: null, time: "TBA", prerequisite: "CS102" },
    { code: "MATH204", name: "Linear Algebra", credits: 3, level: 4, status: "remaining", grade: null, time: "TBA", prerequisite: "MATH102" }
];
let currentCourses = studyPlanCourses.filter(c => c.status === "in-progress");
function getCourseByCode(code) {
    return studyPlanCourses.find(c => c.code === code);
}

function updateStatusInStudyPlan() {
    studyPlanCourses.forEach(course => {
        if (currentCourses.some(c => c.code === course.code)) {
            course.status = "in-progress";
        } else if (course.status !== "completed") {
            course.status = "remaining";
        }
    });
}
function showSection(sectionId) {

    document.getElementById('editSchedule').style.display = 'none';
    document.getElementById('completedCourses').style.display = 'none';
    document.getElementById('remainingCourses').style.display = 'none';
    document.getElementById('studyPlan').style.display = 'none';

    document.getElementById(sectionId).style.display = 'block';

    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    const buttons = document.querySelectorAll('.action-btn');
    const sectionMap = {
        'editSchedule': 0,
        'completedCourses': 1,
        'remainingCourses': 2,
        'studyPlan': 3
    };
    if (buttons[sectionMap[sectionId]]) {
        buttons[sectionMap[sectionId]].classList.add('active');
    }

    if (sectionId === 'editSchedule') {
        displayMySchedule();
        displayAvailableCourses();
    } else if (sectionId === 'completedCourses') {
        displayCompletedCourses();
    } else if (sectionId === 'remainingCourses') {
        displayRemainingCourses();
    } else if (sectionId === 'studyPlan') {
        displayStudyPlan();
    }
}

function displayMySchedule() {
    const tbody = document.getElementById('myScheduleList');
    if (!tbody) return;
    
    if (currentCourses.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center">No courses registered</td></tr>';
        updateTotalCredits();
        return;
    }
    
    tbody.innerHTML = '';
    currentCourses.forEach(course => {
        const row = tbody.insertRow();
        row.insertCell(0).innerHTML = course.code;
        row.insertCell(1).innerHTML = course.name;
        row.insertCell(2).innerHTML = course.credits;
        row.insertCell(3).innerHTML = course.time;
        row.insertCell(4).innerHTML = `<button class="btn-drop" onclick="dropCourse('${course.code}')">Drop</button>`;
    });
    
    updateTotalCredits();
}

function displayAvailableCourses() {
    const tbody = document.getElementById('availableCoursesList');
    if (!tbody) return;
    
    const currentCodes = currentCourses.map(c => c.code);
    const completedCodes = studyPlanCourses.filter(c => c.status === 'completed').map(c => c.code);
    
    let available = studyPlanCourses.filter(c => 
        !currentCodes.includes(c.code) && 
        !completedCodes.includes(c.code) &&
        c.level <= currentStudent.current_level + 1
    );

    available = available.filter(course => {
        if (!course.prerequisite) return true;
        return completedCodes.includes(course.prerequisite) || currentCodes.includes(course.prerequisite);
    });
    
    if (available.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center">No available courses</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    available.forEach(course => {
        const prereqMet = !course.prerequisite || 
            studyPlanCourses.some(c => c.code === course.prerequisite && c.status === 'completed') ||
            currentCourses.some(c => c.code === course.prerequisite);
        
        const row = tbody.insertRow();
        row.insertCell(0).innerHTML = course.code;
        row.insertCell(1).innerHTML = course.name;
        row.insertCell(2).innerHTML = course.credits;
        row.insertCell(3).innerHTML = `Level ${course.level}`;
        row.insertCell(4).innerHTML = course.time;
        row.insertCell(5).innerHTML = prereqMet ? ' Met' : `Need: ${course.prerequisite}`;
        row.insertCell(6).innerHTML = prereqMet ? 
            `<button class="btn-register" onclick="registerCourse('${course.code}')">Register</button>` : 
            '<button class="btn-disabled" disabled>Locked</button>';
    });
}

function registerCourse(courseCode) {
    const course = getCourseByCode(courseCode);
    if (!course) return;
    
    if (currentCourses.some(c => c.code === courseCode)) {
        showAlert(`Already registered for ${courseCode}`, 'error');
        return;
    }
    
    currentCourses.push({
        code: course.code,
        name: course.name,
        credits: course.credits,
        time: course.time,
        status: 'in-progress',
        grade: null,
        level: course.level,
        prerequisite: course.prerequisite
    });
    
    updateStatusInStudyPlan();
    displayMySchedule();
    displayAvailableCourses();
    displayStudyPlan();
    showAlert(`${courseCode} registered successfully!`, 'success');
}

function dropCourse(courseCode) {
    if (confirm(`Are you sure you want to drop ${courseCode}?`)) {
        currentCourses = currentCourses.filter(c => c.code !== courseCode);
        updateStatusInStudyPlan();
        displayMySchedule();
        displayAvailableCourses();
        displayStudyPlan();
        showAlert(`${courseCode} dropped successfully!`, 'success');
    }
}

function dropAllCourses() {
    if (confirm('WARNING: Drop ALL current courses?')) {
        currentCourses = [];
        updateStatusInStudyPlan();
        displayMySchedule();
        displayAvailableCourses();
        displayStudyPlan();
        showAlert('All courses dropped!', 'success');
    }
}

function updateTotalCredits() {
    const totalDiv = document.getElementById('totalCredits');
    if (totalDiv) {
        let total = 0;
        currentCourses.forEach(c => total += c.credits);
        totalDiv.innerHTML = `Total Credits: ${total} / 18`;
    }
}

function displayCompletedCourses() {
    const tbody = document.getElementById('completedCoursesList');
    if (!tbody) return;
    
    const completed = studyPlanCourses.filter(c => c.status === 'completed');
    
    document.getElementById('completedCount').innerHTML = completed.length;
    const totalCredits = completed.reduce((sum, c) => sum + c.credits, 0);
    document.getElementById('completedCredits').innerHTML = totalCredits;
    
    if (completed.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center">No completed courses</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    completed.forEach(course => {
        const row = tbody.insertRow();
        row.insertCell(0).innerHTML = `Level ${course.level}`;
        row.insertCell(1).innerHTML = course.code;
        row.insertCell(2).innerHTML = course.name;
        row.insertCell(3).innerHTML = course.credits;
        row.insertCell(4).innerHTML = course.grade || 'A';
    });
}

function displayRemainingCourses() {
    const tbody = document.getElementById('remainingCoursesList');
    if (!tbody) return;
    
    const remaining = studyPlanCourses.filter(c => c.status === 'remaining');
    
    document.getElementById('remainingCount').innerHTML = remaining.length;
    const totalCredits = remaining.reduce((sum, c) => sum + c.credits, 0);
    document.getElementById('remainingCredits').innerHTML = totalCredits;
    
    if (remaining.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center">No remaining courses</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    remaining.forEach(course => {
        const row = tbody.insertRow();
        row.insertCell(0).innerHTML = `Level ${course.level}`;
        row.insertCell(1).innerHTML = course.code;
        row.insertCell(2).innerHTML = course.name;
        row.insertCell(3).innerHTML = course.credits;
        row.insertCell(4).innerHTML = course.prerequisite || 'None';
    });
}

function displayStudyPlan() {
    const tbody = document.getElementById('studyPlanList');
    if (!tbody) return;
    
    updateStatusInStudyPlan();
    
    tbody.innerHTML = '';
    
    studyPlanCourses.forEach(course => {
        const row = tbody.insertRow();
        row.insertCell(0).innerHTML = `Level ${course.level}`;
        row.insertCell(1).innerHTML = course.code;
        row.insertCell(2).innerHTML = course.name;
        row.insertCell(3).innerHTML = course.credits;
        
        let statusHtml = '';
        if (course.status === 'completed') {
            statusHtml = '<span class="status-passed">Completed</span>';
        } else if (course.status === 'in-progress') {
            statusHtml = '<span class="status-progress">In Progress</span>';
        } else {
            statusHtml = '<span class="status-remaining">Not Started</span>';
        }
        row.insertCell(4).innerHTML = statusHtml;
    });
}

function showAlert(message, type) {
    const existingAlert = document.querySelector('.alert-message');
    if (existingAlert) existingAlert.remove();
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `${type} alert-message`;
    alertDiv.innerHTML = message;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => alertDiv.remove(), 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('student-dashboard.html')) {
        displayStudyPlan();
        displayMySchedule();
        displayAvailableCourses();
        displayCompletedCourses();
        displayRemainingCourses();

        document.getElementById('studyPlan').style.display = 'block';
        document.getElementById('editSchedule').style.display = 'none';
        document.getElementById('completedCourses').style.display = 'none';
        document.getElementById('remainingCourses').style.display = 'none';
        const buttons = document.querySelectorAll('.action-btn');
        if (buttons[3]) buttons[3].classList.add('active');
    }
});

function validateLogin() {
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const userType = document.querySelector('input[name="userType"]:checked');
    
    if (!email.value.includes('@')) {
        showAlert('Please enter a valid email', 'error');
        email.focus();
        return false;
    }
    
    if (password.value.length < 3) {
        showAlert('Password must be at least 3 characters', 'error');
        password.focus();
        return false;
    }
    
    if (userType.value === 'student') {
        window.location.href = 'student-dashboard.html';
    } else {
        window.location.href = 'admin/admin-dashboard.html';
    }
    return false;
}

function validateRegister() {
    const studentId = document.getElementById('studentId');
    const fullName = document.getElementById('fullName');
    const email = document.getElementById('email');
    const major = document.getElementById('major');
    const yearLevel = document.getElementById('yearLevel');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const terms = document.getElementById('terms');
    
    if (studentId.value.trim() === '' || studentId.value.length < 9) {
        showAlert('Please enter a valid Student ID (9 digits)', 'error');
        studentId.focus();
        return false;
    }
    if (fullName.value.trim() === '') {
        showAlert('Please enter your full name', 'error');
        fullName.focus();
        return false;
    }
    if (!email.value.includes('@')) {
        showAlert('Please enter a valid email', 'error');
        email.focus();
        return false;
    }
    if (major.value === '') {
        showAlert('Please select your major', 'error');
        major.focus();
        return false;
    }
    if (yearLevel.value === '') {
        showAlert('Please select your year level', 'error');
        yearLevel.focus();
        return false;
    }
    if (password.value.length < 6) {
        showAlert('Password must be at least 6 characters', 'error');
        password.focus();
        return false;
    }
    if (password.value !== confirmPassword.value) {
        showAlert('Passwords do not match', 'error');
        confirmPassword.focus();
        return false;
    }
    if (!terms.checked) {
        showAlert('You must agree to the Terms', 'error');
        return false;
    }
    
    showAlert('Registration successful! Redirecting...', 'success');
    setTimeout(() => window.location.href = 'index.html', 2000);
    return false;
}
const showPasswordCheckbox = document.getElementById('showPassword');
if (showPasswordCheckbox) {
    showPasswordCheckbox.addEventListener('change', function() {
        const passwordField = document.getElementById('password');
        if (passwordField) {
            passwordField.type = this.checked ? 'text' : 'password';
        }
    });
}

const showPasswordReg = document.getElementById('showPasswordReg');
if (showPasswordReg) {
    showPasswordReg.addEventListener('change', function() {
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('confirmPassword');
        if (passwordField) {
            passwordField.type = this.checked ? 'text' : 'password';
        }
        if (confirmField) {
            confirmField.type = this.checked ? 'text' : 'password';
        }
    });
}