
document.addEventListener("DOMContentLoaded", function () {

    
    // Add this array at the top of your dashboard.js file
    const availableCourses = [
        { code: "ML201", name: "Maritime Law" },
        { code: "NS302", name: "Navigation Systems" },
        { code: "ME101", name: "Marine Engineering" },
        { code: "OC205", name: "Oceanography" },
        { code: "SM401", name: "Ship Management" },
        { code: "MT103", name: "Marine Technology" },
        { code: "PS202", name: "Port Security" },
        { code: "SC301", name: "Shipping Commerce" },
    ]

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
  
    assignCourseBtn.addEventListener("click", function () {
        openModal("assignCourseModal")
    })

    closeAssignCourseModal.addEventListener("click", function () {
        closeModal("assignCourseModal")
    })

    cancelAssignCourse.addEventListener("click", function () {
        closeModal("assignCourseModal")
    })
  
    submitAssignCourse.addEventListener("click", function () {
        // Validate form
        const form = document.getElementById("assignCourseForm")
        const courseSelect = document.getElementById("courseSelect")
        const lecturerSelect = document.getElementById("lecturerSelect")
        const semesterSelect = document.getElementById("semesterSelect")
        const departmentSelect = document.getElementById("departmentSelect")
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
            department: departmentSelect.value,
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
  
    uploadCoursesBtn.addEventListener("click", function () { 
      openModal("uploadCoursesModal")
    })
  
    closeUploadCoursesModal.addEventListener("click", function () {
      closeModal("uploadCoursesModal")
    })
  
    cancelUploadCourses.addEventListener("click", function () {
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
  
    submitUploadCourses.addEventListener("click", async function () {
        const activeTab = document.querySelector(".tab-content.active").id
  
        if (activeTab === "bulkUpload") {
            if (!courseFileUpload.files.length) {
                alert("Please select a file to upload");
                return;
            }
  
            // Simulate file upload
            const courseFile = courseFileUpload.files[0];
            const department = document.getElementById("courseDepartment");

            if (!department.value) {
                alert("Please fill in all required fields");
                return;
            }

            const formData = new FormData();
            formData.append("courseFile", courseFile);
            formData.append("departmentId", department.value);
            
            $.ajax({
                type: "POST",
                url: "../endpoint/upload-courses",
                data: formData,
                processData: false,
                contentType: false,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        alert(result.message);
                        closeModal('uploadCoursesModal');
                        document.getElementById("bulkUploadForm").reset();
                    } else {
                        alert(result.message);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });


        } else {
            // Single course form validation
            const courseCode = document.getElementById("courseCode");
            const courseName = document.getElementById("courseName");
            const creditHours = document.getElementById("creditHours");
            const contactHours = document.getElementById("contactHours");
            const courseLevel = document.getElementById("courseLevel");
            const courseCategory = document.getElementById("courseCategory");
            const courseSemester = document.getElementById("courseSemester");
            const department = document.getElementById("courseDepartment");
    
            if ( !courseCode.value || !courseName.value || !creditHours.value || !contactHours.value || !department.value || !courseLevel.value || !courseSemester.value ) {
                alert("Please fill in all required fields");
                return;
            }
    
            // Simulate API call
            const formData = {
                courseCode: courseCode.value,
                courseName: courseName.value,
                creditHours: creditHours.value,
                contactHours: contactHours.value,
                semester: courseSemester.value,
                level: courseLevel.value,
                category: courseCategory.value,
                departmentId: department.value,
            };

            $.ajax({
                type: "POST",
                url: "../endpoint/add-course",
                data: formData,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        alert(result.message);
                        closeModal('uploadCoursesModal');
                        document.getElementById("addCourseForm").reset();
                    } else {
                        alert(result['message']);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
    })
  
    // Set Deadline Modal
    const setDeadlineBtn = document.getElementById("setDeadlineBtn")
    const closeSetDeadlineModal = document.getElementById("closeSetDeadlineModal")
    const cancelSetDeadline = document.getElementById("cancelSetDeadline")
    const submitSetDeadline = document.getElementById("submitSetDeadline")
  
    setDeadlineBtn.addEventListener("click", function () {
      openModal("setDeadlineModal")
    })
  
    closeSetDeadlineModal.addEventListener("click", function () {
      closeModal("setDeadlineModal")
    })
  
    cancelSetDeadline.addEventListener("click", function () {
      closeModal("setDeadlineModal")
    })
  
    submitSetDeadline.addEventListener("click", function () {
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
        };
  
        console.log("Setting deadline:", formData);
  
        // Simulate successful setting
        setTimeout(function () {
          alert("Deadline set successfully!");
          closeModal("setDeadlineModal");
          document.getElementById("singleDeadlineForm").reset();
          document.getElementById("selectedCoursesList").innerHTML = "";
          document.getElementById("noCoursesMessage").style.display = "block";
        }, 1000)
      } else {
        // Bulk deadline validation
        const checkboxes = document.querySelectorAll('#bulkDeadlineForm input[type="checkbox"]:checked')
        const bulkDeadlineDate = document.getElementById("bulkDeadlineDate");
  
        if (checkboxes.length === 0 || !bulkDeadlineDate.value) {
          alert("Please select at least one course and a deadline date");
          return;
        };
  
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
        };
  
        console.log("Setting bulk deadlines:", formData);
  
        // Simulate successful setting
        setTimeout(function () {
          alert("Deadlines set successfully!");
          closeModal("setDeadlineModal");
          document.getElementById("bulkDeadlineForm").reset();
        }, 1000)
      }
    })
  
    // View Results Modal
    const viewResultsBtn = document.getElementById("viewResultsBtn")
    const closeViewResultsModal = document.getElementById("closeViewResultsModal")
    const closeResultsBtn = document.getElementById("closeResultsBtn")
  
    viewResultsBtn.addEventListener("click", function () {
      openModal("viewResultsModal")
    })
  
    closeViewResultsModal.addEventListener("click", function () {
      closeModal("viewResultsModal")
    })
  
    closeResultsBtn.addEventListener("click", function () {
      closeModal("viewResultsModal")
    })
  
    // Results filtering
    const filterCourse = document.getElementById("filterCourse")
    const filterLecturer = document.getElementById("filterLecturer")
    const filterStatus = document.getElementById("filterStatus")
    ;[filterCourse, filterLecturer, filterStatus].forEach((filter) => {
      filter.addEventListener("change", function () {
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
  
    closeCourseResultsDetailModal.addEventListener("click", function () {
      closeModal("courseResultsDetailModal")
    })
  
    backToResultsBtn.addEventListener("click", function () { 
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
  
    exportResultsBtn.addEventListener("click", function () { 
      console.log("Exporting results report")
  
      // In a real application, you would generate and download a report
      alert("Exporting results report")
    })
  
    // Download detailed results
    const downloadDetailedResultsBtn = document.getElementById("downloadDetailedResultsBtn")
  
    downloadDetailedResultsBtn.addEventListener("click", function () { 
      console.log("Downloading detailed results report")
  
      // In a real application, you would generate and download a detailed report
      alert("Downloading detailed results report")
    });
  
  // Add these functions to your dashboard.js file
  function openCourseSelectionModal() {
    document.getElementById("courseSelectionModal").classList.add("active")
  }
  
  function closeCourseSelectionModal() {
    document.getElementById("courseSelectionModal").classList.remove("active")
  }
  
  function searchCourses() {
    const searchTerm = document.getElementById("courseSearchInput").value.toLowerCase()
    const courseList = document.getElementById("courseList")
    courseList.innerHTML = ""
  
    availableCourses.forEach((course) => {
      if (course.code.toLowerCase().includes(searchTerm) || course.name.toLowerCase().includes(searchTerm)) {
        const courseItem = document.createElement("div")
        courseItem.className = "course-item"
        courseItem.innerHTML = `
          <div class="course-info">
            <strong>${course.code}</strong> - ${course.name}
          </div>
          <button class="add-course-btn" data-code="${course.code}" data-name="${course.name}">
            <i class="fas fa-plus"></i>
          </button>
        `
        courseList.appendChild(courseItem)
      }
    })
  
    // Add event listeners to the add buttons
    document.querySelectorAll(".add-course-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        const code = this.getAttribute("data-code")
        const name = this.getAttribute("data-name")
        addCourseToSelection(code, name)
      })
    })
  }
  
  function addCourseToSelection(code, name) {
    const selectedCoursesList = document.getElementById("selectedCoursesList")
  
    // Check if course is already added
    if (document.querySelector(`.selected-course[data-code="${code}"]`)) {
      return
    }
  
    const courseItem = document.createElement("div")
    courseItem.className = "selected-course"
    courseItem.setAttribute("data-code", code)
    courseItem.innerHTML = `
      <div class="course-info">
        <strong>${code}</strong> - ${name}
      </div>
      <button class="remove-course-btn" data-code="${code}">
        <i class="fas fa-times"></i>
      </button>
      <input type="hidden" name="selectedCourses[]" value="${code}">
    `
    selectedCoursesList.appendChild(courseItem)
  
    // Add event listener to the remove button
    courseItem.querySelector(".remove-course-btn").addEventListener("click", function () {
      const code = this.getAttribute("data-code")
      removeFromSelection(code)
    })
  }
  
  function removeFromSelection(code) {
    const courseItem = document.querySelector(`.selected-course[data-code="${code}"]`)
    if (courseItem) {
      courseItem.remove()
    }
  }
  
  function confirmCourseSelection() {
    closeCourseSelectionModal()
  }
  
  // Add this to your document.addEventListener("DOMContentLoaded", function() { ... }) block
  document.addEventListener("DOMContentLoaded", () => {
    // Your existing code...
  
    // Course Selection Modal
    const selectCoursesBtn = document.getElementById("selectCoursesBtn")
    const closeCourseSelectionModal = document.getElementById("closeCourseSelectionModal")
    const confirmCourseSelectionBtn = document.getElementById("confirmCourseSelectionBtn")
    const courseSearchInput = document.getElementById("courseSearchInput")
  
    if (selectCoursesBtn) {
      selectCoursesBtn.addEventListener("click", () => {
        openCourseSelectionModal()
      })
    }
  
    if (closeCourseSelectionModal) {
      closeCourseSelectionModal.addEventListener("click", () => {
        closeCourseSelectionModal()
      })
    }
  
    if (confirmCourseSelectionBtn) {
      confirmCourseSelectionBtn.addEventListener("click", () => {
        confirmCourseSelection()
      })
    }
  
    if (courseSearchInput) {
      courseSearchInput.addEventListener("input", () => {
        searchCourses()
      })
  
      // Initialize course list on modal open
      courseSearchInput.addEventListener("focus", () => {
        if (courseSearchInput.value === "") {
          searchCourses()
        }
      })
    }
  
    // Initialize course list when modal opens
    if (selectCoursesBtn) {
      selectCoursesBtn.addEventListener("click", () => {
        setTimeout(() => {
          searchCourses()
        }, 100)
      })
    }
  })
  
  })