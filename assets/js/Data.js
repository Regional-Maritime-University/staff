// Create a global object to hold shared data
window.AppData = {
    COURSES: [],
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
                semesterRes,
                coursesRes,
                staffRes,
                // deadlinesRes,
                // resultsRes,
                // studentsRes,
                // messagesRes,
                // notificationsRes
            ] = await Promise.all([
                fetch(`../endpoint/current-semesters`, { method: 'GET', headers: {'Content-Type': 'application/json'}}),
                fetch(`../endpoint/fetch-course`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ departmentId }) }),
                fetch(`../endpoint/fetch-staff`, { method: 'POST', headers: {'Content-Type': 'application/json'}}),
                // fetch(`../endpoint/fetch-deadline`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ departmentId }) }),
                // fetch(`../endpoint/fetch-result`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ departmentId }) }),
                // fetch(`../endpoint/fetch-student`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ departmentId }) }),
                // fetch(`../endpoint/fetch-message`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ userId }) }),
                // fetch(`../endpoint/fetch-notification`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ userId }) })
            ]);

            const coursesData = await coursesRes.json();
            const staffData = await staffRes.json();
            // const deadlinesData = await deadlinesRes.json();
            // const resultsData = await resultsRes.json();
            // const studentsData = await studentsRes.json();
            // const messagesData = await messagesRes.json();
            // const notificationsData = await notificationsRes.json();

            // Populate global object
            window.AppData.COURSES = coursesData.data;
            window.AppData.LECTURERS_AND_HODS = staffData.data.filter(s => s.role === 'lecturer' || s.role === 'hod');
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
