document.addEventListener("DOMContentLoaded", () => {
    // Sidebar Toggle
    const sidebar = document.getElementById("sidebar")
    const toggleSidebarBtn = document.getElementById("toggleSidebar")
    const mainContent = document.querySelector(".main-content")
  
    toggleSidebarBtn.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed")

        // For mobile
        if (window.innerWidth <= 768) {
        sidebar.classList.toggle("active")
        }
    })
  
    // Close sidebar when clicking outside on mobile
    mainContent.addEventListener("click", () => {
        if (window.innerWidth <= 768 && sidebar.classList.contains("active")) {
        sidebar.classList.remove("active")
        }
    })
  
    // Modal Functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.add("active")
        document.body.style.overflow = "hidden"
    }
  
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove("active")
        document.body.style.overflow = "auto"
    }
  
    // Close modal when clicking outside
    document.querySelectorAll(".modal").forEach((modal) => {
        modal.addEventListener("click", function (e) {
        if (e.target === this) {
            this.classList.remove("active")
            document.body.style.overflow = "auto"
        }
        })
    })
  
    // Assign Course Modal
    const assignCourseBtn = document.getElementById("assignCourseBtn")
    const closeAssignCourseModal = document.getElementById("closeAssignCourseModal")
    const cancelAssignCourse = document.getElementById("cancelAssignCourse")
    const submitAssignCourse = document.getElementById("submitAssignCourse")
  
    assignCourseBtn.addEventListener("click", () => {
        openModal("assignCourseModal")
    })

    closeAssignCourseModal.addEventListener("click", () => {
        closeModal("assignCourseModal")
    })

    cancelAssignCourse.addEventListener("click", () => {
        closeModal("assignCourseModal")
    })
  
    submitAssignCourse.addEventListener("click", () => {
        // Validate form
        const form = document.getElementById("assignCourseForm")
        const courseSelect = document.getElementById("courseSelect")
        const lecturerSelect = document.getElementById("lecturerSelect")
        const semesterSelect = document.getElementById("semesterSelect")
        const assignmentNotes = document.getElementById("assignmentNotes")
    
        if (!courseSelect.value || !lecturerSelect.value || !semesterSelect.value) {
            alert("Please fill in all required fields")
            return
        }
  
        // Simulate API call
        const formData = {
            course: courseSelect.value,
            lecturer: lecturerSelect.value,
            semester: semesterSelect.value,
            notes: assignmentNotes.value,
        }

        $.ajax({
            type: "POST",
            url: "../endpoint/assign-course",
            data: formData,
            success: function(result) {
                console.log(result);
                if (result.success) {
                    alert(result.message);
                    closeModal('assignCourseModal');
                    form.reset();
                } else {
                    alert(result['message']);
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    });
  
    // Upload Courses Modal
    const uploadCoursesBtn = document.getElementById("uploadCoursesBtn")
    const closeUploadCoursesModal = document.getElementById("closeUploadCoursesModal")
    const cancelUploadCourses = document.getElementById("cancelUploadCourses")
    const submitUploadCourses = document.getElementById("submitUploadCourses")
  
    uploadCoursesBtn.addEventListener("click", () => {
      openModal("uploadCoursesModal")
    })
  
    closeUploadCoursesModal.addEventListener("click", () => {
      closeModal("uploadCoursesModal")
    })
  
    cancelUploadCourses.addEventListener("click", () => {
      closeModal("uploadCoursesModal")
    })
  
    // Tab functionality for Upload Courses Modal
    const tabBtns = document.querySelectorAll(".tab-btn")
  
    tabBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const tabId = this.getAttribute("data-tab")
  
        // Remove active class from all tabs and contents
        tabBtns.forEach((btn) => btn.classList.remove("active"))
        document.querySelectorAll(".tab-content").forEach((content) => content.classList.remove("active"))
  
        // Add active class to clicked tab and corresponding content
        this.classList.add("active")
        document.getElementById(tabId).classList.add("active")
      })
    })
  
    // File upload handling
    const courseFileUpload = document.getElementById("courseFileUpload")
    const selectedFileName = document.getElementById("selectedFileName")
  
    courseFileUpload.addEventListener("change", function () {
      if (this.files.length > 0) {
        selectedFileName.textContent = this.files[0].name
      } else {
        selectedFileName.textContent = "No file selected"
      }
    })
  
    submitUploadCourses.addEventListener("click", () => {
      const activeTab = document.querySelector(".tab-content.active").id
  
      if (activeTab === "bulkUpload") {
        if (!courseFileUpload.files.length) {
          alert("Please select a file to upload")
          return
        }
  
        // Simulate file upload
        const file = courseFileUpload.files[0]
        console.log("Uploading file:", file.name)
  
        // Simulate successful upload
        setTimeout(() => {
          alert("Courses uploaded successfully!")
          closeModal("uploadCoursesModal")
          selectedFileName.textContent = "No file selected"
          courseFileUpload.value = ""
        }, 1500)
  
        // In a real application, you would use FormData with fetch:
        /*
              const formData = new FormData();
              formData.append('courseFile', file);
              
              fetch('/api/upload-courses', {
                  method: 'POST',
                  body: formData
              })
              .then(response => response.json())
              .then(data => {
                  alert('Courses uploaded successfully!');
                  closeModal('uploadCoursesModal');
                  selectedFileName.textContent = 'No file selected';
                  courseFileUpload.value = '';
              })
              .catch(error => {
                  console.error('Error:', error);
                  alert('An error occurred while uploading courses');
              });
              */
      } else {
        // Single course form validation
        const courseCode = document.getElementById("courseCode")
        const courseTitle = document.getElementById("courseTitle")
        const creditHours = document.getElementById("creditHours")
        const department = document.getElementById("department")
        const courseLevel = document.getElementById("courseLevel")
        const courseSemester = document.getElementById("courseSemester")
  
        if (
          !courseCode.value ||
          !courseTitle.value ||
          !creditHours.value ||
          !department.value ||
          !courseLevel.value ||
          !courseSemester.value
        ) {
          alert("Please fill in all required fields")
          return
        }
  
        // Simulate API call
        const formData = {
          courseCode: courseCode.value,
          courseTitle: courseTitle.value,
          creditHours: creditHours.value,
          departmentId: department.value,
          level: courseLevel.value,
          semester: courseSemester.value,
        }
  
        console.log("Adding course:", formData)
  
        // Simulate successful addition
        setTimeout(() => {
          alert("Course added successfully!")
          closeModal("uploadCoursesModal")
          document.getElementById("addCourseForm").reset()
        }, 1000)
      }
    })
  
    // Set Deadline Modal
    const setDeadlineBtn = document.getElementById("setDeadlineBtn")
    const closeSetDeadlineModal = document.getElementById("closeSetDeadlineModal")
    const cancelSetDeadline = document.getElementById("cancelSetDeadline")
    const submitSetDeadline = document.getElementById("submitSetDeadline")
  
    setDeadlineBtn.addEventListener("click", () => {
      openModal("setDeadlineModal")
    })
  
    closeSetDeadlineModal.addEventListener("click", () => {
      closeModal("setDeadlineModal")
    })
  
    cancelSetDeadline.addEventListener("click", () => {
      closeModal("setDeadlineModal")
    })
  
    submitSetDeadline.addEventListener("click", () => {
      const activeTab = document.querySelector(".tab-content.active").id
  
      if (activeTab === "singleDeadline") {
        const deadlineCourse = document.getElementById("deadlineCourse")
        const deadlineDate = document.getElementById("deadlineDate")
  
        if (!deadlineCourse.value || !deadlineDate.value) {
          alert("Please select a course and deadline date")
          return
        }
  
        // Simulate API call
        const formData = {
          courseId: deadlineCourse.value,
          deadlineDate: deadlineDate.value,
          notes: document.getElementById("deadlineNotes").value,
        }
  
        console.log("Setting deadline:", formData)
  
        // Simulate successful setting
        setTimeout(() => {
          alert("Deadline set successfully!")
          closeModal("setDeadlineModal")
          document.getElementById("singleDeadlineForm").reset()
        }, 1000)
      } else {
        // Bulk deadline validation
        const checkboxes = document.querySelectorAll('#bulkDeadlineForm input[type="checkbox"]:checked')
        const bulkDeadlineDate = document.getElementById("bulkDeadlineDate")
  
        if (checkboxes.length === 0 || !bulkDeadlineDate.value) {
          alert("Please select at least one course and a deadline date")
          return
        }
  
        // Collect selected courses
        const selectedCourses = []
        checkboxes.forEach((checkbox) => {
          selectedCourses.push(checkbox.value)
        })
  
        // Simulate API call
        const formData = {
          courseIds: selectedCourses,
          deadlineDate: bulkDeadlineDate.value,
          notes: document.getElementById("bulkDeadlineNotes").value,
        }
  
        console.log("Setting bulk deadlines:", formData)
  
        // Simulate successful setting
        setTimeout(() => {
          alert("Deadlines set successfully!")
          closeModal("setDeadlineModal")
          document.getElementById("bulkDeadlineForm").reset()
        }, 1000)
      }
    })
  
    // View Results Modal
    const viewResultsBtn = document.getElementById("viewResultsBtn")
    const closeViewResultsModal = document.getElementById("closeViewResultsModal")
    const closeResultsBtn = document.getElementById("closeResultsBtn")
  
    viewResultsBtn.addEventListener("click", () => {
      openModal("viewResultsModal")
    })
  
    closeViewResultsModal.addEventListener("click", () => {
      closeModal("viewResultsModal")
    })
  
    closeResultsBtn.addEventListener("click", () => {
      closeModal("viewResultsModal")
    })
  
    // Results filtering
    const filterCourse = document.getElementById("filterCourse")
    const filterLecturer = document.getElementById("filterLecturer")
    const filterStatus = document.getElementById("filterStatus")
    ;[filterCourse, filterLecturer, filterStatus].forEach((filter) => {
      filter.addEventListener("change", () => {
        // In a real application, you would filter the results table based on the selected filters
        console.log("Filtering results:", {
          course: filterCourse.value,
          lecturer: filterLecturer.value,
          status: filterStatus.value,
        })
      })
    })
  
    // View detailed results
    const viewResultsBtns = document.querySelectorAll(".view-results")
    const closeCourseResultsDetailModal = document.getElementById("closeCourseResultsDetailModal")
    const backToResultsBtn = document.getElementById("backToResultsBtn")
  
    viewResultsBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const courseId = this.getAttribute("data-course")
        console.log("Viewing results for course:", courseId)
  
        // In a real application, you would fetch the course results data
        // and populate the detailed results modal
  
        closeModal("viewResultsModal")
        openModal("courseResultsDetailModal")
      })
    })
  
    closeCourseResultsDetailModal.addEventListener("click", () => {
      closeModal("courseResultsDetailModal")
    })
  
    backToResultsBtn.addEventListener("click", () => {
      closeModal("courseResultsDetailModal")
      openModal("viewResultsModal")
    })
  
    // Results tabs in detailed view
    const resultsTabs = document.querySelectorAll(".results-tabs .tab-btn")
  
    resultsTabs.forEach((tab) => {
      tab.addEventListener("click", function () {
        const tabId = this.getAttribute("data-tab")
  
        resultsTabs.forEach((t) => t.classList.remove("active"))
        document.querySelectorAll("#courseResultsDetailModal .tab-content").forEach((content) => {
          content.classList.remove("active")
        })
  
        this.classList.add("active")
        document.getElementById(tabId).classList.add("active")
      })
    })
  
    // Download results
    const downloadResultsBtns = document.querySelectorAll(".download-results")
  
    downloadResultsBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const courseId = this.getAttribute("data-course")
        console.log("Downloading results for course:", courseId)
  
        // In a real application, you would trigger a download of the results file
        alert(`Downloading results for course ${courseId}`)
      })
    })
  
    // Remind lecturer
    const remindLecturerBtns = document.querySelectorAll(".remind-lecturer")
  
    remindLecturerBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const courseId = this.getAttribute("data-course")
        console.log("Sending reminder for course:", courseId)
  
        // In a real application, you would send a reminder to the lecturer
        alert(`Reminder sent to lecturer for course ${courseId}`)
      })
    })
  
    // Export results report
    const exportResultsBtn = document.getElementById("exportResultsBtn")
  
    exportResultsBtn.addEventListener("click", () => {
      console.log("Exporting results report")
  
      // In a real application, you would generate and download a report
      alert("Exporting results report")
    })
  
    // Download detailed results
    const downloadDetailedResultsBtn = document.getElementById("downloadDetailedResultsBtn")
  
    downloadDetailedResultsBtn.addEventListener("click", () => {
      console.log("Downloading detailed results report")
  
      // In a real application, you would generate and download a detailed report
      alert("Downloading detailed results report")
    })
  })
  