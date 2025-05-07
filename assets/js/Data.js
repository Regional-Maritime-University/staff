// Create a global object to hold shared data
window.AppData = {
    ACTIVE_SEMESTERS: [], 
    COURSES: [],
    ASSIGNED_COURSES: [],
    LECTURERS_AND_HODS: [],
    DEADLINES: [],
    RESULTS: [],
    STUDENTS: [],
    MESSAGES: [],
    NOTIFICATIONS: [],
};

document.addEventListener("DOMContentLoaded", function () {

    window.AppData.user = window.AuthenticatedStaff;
    const user = window.AppData.user;
    
    const departmentId = user ? user.department_id : null;
    const userId = user ? user.number : null;

    if (departmentId === null || departmentId === undefined) {
        console.error("Department ID is not available. Cannot fetch data.");
        return;
    }

    if (userId === null || userId === undefined) {
        console.error("User ID is not available. Cannot fetch data.");
        return;
    }

    async function fetchData() {
        try {
            const [
                activeSemestersRes,
                coursesRes,
                assignedCoursesRes,
                staffRes,
                // deadlinesRes,
                // resultsRes,
                // studentsRes,
                // messagesRes,
                // notificationsRes
            ] = await Promise.all([
                fetch(`../endpoint/active-semesters`, { method: 'GET', headers: {'Content-Type': 'application/x-www-form-urlencoded'}}),
                fetch(`../endpoint/fetch-course`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ departmentId }) }),
                fetch(`../endpoint/fetch-assigned-courses`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: new URLSearchParams({ department: departmentId }).toString() }),
                fetch(`../endpoint/fetch-staff`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}}),
                // fetch(`../endpoint/fetch-deadline`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ departmentId }) }),
                // fetch(`../endpoint/fetch-result`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ departmentId }) }),
                // fetch(`../endpoint/fetch-student`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ departmentId }) }),
                // fetch(`../endpoint/fetch-message`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ userId }) }),
                // fetch(`../endpoint/fetch-notification`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: JSON.stringify({ userId }) })
            ]);

            const activeSemestersData = await activeSemestersRes.json();
            const coursesData = await coursesRes.json();
            const assignedCoursesData = await assignedCoursesRes.json();
            const staffData = await staffRes.json();
            // const deadlinesData = await deadlinesRes.json();
            // const resultsData = await resultsRes.json();
            // const studentsData = await studentsRes.json();
            // const messagesData = await messagesRes.json();
            // const notificationsData = await notificationsRes.json();

            // Populate global object
            if (activeSemestersData.success) window.AppData.ACTIVE_SEMESTERS = assignedCoursesData.data;
            if (activeSemestersData.success) window.AppData.COURSES = coursesData.data;
            if (activeSemestersData.success) window.AppData.ASSIGNED_COURSES = assignedCoursesData.data;
            if (activeSemestersData.success) window.AppData.LECTURERS_AND_HODS = window.AppData.LECTURERS_AND_HODS = staffData.data.filter(s => s.role === 'lecturer' || s.role === 'hod');
            // window.AppData.DEADLINES = deadlinesData.data;
            // window.AppData.RESULTS = resultsData.data;
            // window.AppData.STUDENTS = studentsData.data;
            // window.AppData.MESSAGES = messagesData.data;
            // window.AppData.NOTIFICATIONS = notificationsData.data;

            // Optional: Dispatch a custom event to notify that data is ready
            document.dispatchEvent(new CustomEvent('AppDataReady'));

        } catch (error) {
            console.error("Error fetching data:", error);
        }
    }

    fetchData();
});
