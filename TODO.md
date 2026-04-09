### Phase 1: Core System & Installation
- [ ] **Dynamic Configuration:**
  - [ ] Implement settings table/module for dynamic school properties (Name, Logo, Address, etc.).
- [ ] **Installation Wizard:**
  - [ ] Create a web-based installer (similar to WordPress) for initial database setup and admin creation.
- [ ] **Database Setup:**
  - [ ] Design and refine database schema to support all features.
  - [ ] Create seeders and fresh migrations (dropping existing DB as requested for a clean slate).

### Phase 2: User Roles & Authentication
- [ ] **Role Management:**
  - [ ] Implement role-based access control (RBAC) for `Admin`, `Teacher`, `Accountant`, and `Student/Parent`.
- [ ] **Admin Features:**
  - [ ] Manage Academic Structure (Sessions, Terms, Classes, Subjects, Grades).
  - [ ] Set "Active" Session and Term.
  - [ ] User Management (Register staff/teachers, manage roles).
  - [ ] Assign classes, subjects, and duties to teachers.
  - [ ] Timetable management module.
  - [ ] Announcements system (target: students, teachers, or general).

### Phase 3: Student Management & Workflows
- [ ] **Registration & Onboarding:**
  - [ ] Implement student registration (manual entry by admin or self-registration).
  - [ ] Support Excel bulk upload for students.
  - [ ] Student profile management (including passport photo upload).
  - [ ] Auto-generate Admission Numbers and printable Admission Letters.
- [ ] **Student Status & Promotion:**
  - [ ] Implement Admission Statuses (`pending`, `admitted`, `withdrawn`).
  - [ ] Implement Student Categories (`New Intake (NI)`, `Old Student (OS)`). Logic to automatically switch NI to OS after their first term.
  - [ ] Automated term/class promotion system (progressively moving students to the next term/class) with admin override capabilities.
- [ ] **Student Portal:**
  - [ ] View biodata, scores, and payment status.
  - [ ] View and print receipts, report sheets, attendance, and transcripts.
  - [ ] View admin notifications.
  - [ ] Helpdesk: Create and manage complaint/suggestion tickets.
  - [ ] (Optional) Submit assignments interface.

### Phase 4: Financial Management (Fees & Payments)
- [ ] **Fee Structure:**
  - [ ] Create dynamic fees based on: `Fee Name`, `Category (NI/OS)`, `Gender (M/F)`, `Class`, `Session`, and `Term`.
  - [ ] Automatic fee assignment to students based on their profile and current term/class.
  - [ ] Manual fee override (assign extra specific fees or remove fees per student).
- [ ] **Payments:**
  - [ ] Online payment integration on the student portal.
  - [ ] Manual payment entry by Admins/Accountants.
  - [ ] Payment modification (additions, deductions).
  - [ ] Advanced filtering and sorting (e.g., filter students by "Bus Fee" for a specific term).
- [ ] **Payroll:**
  - [ ] Implement payroll management for staff and teachers (accessible by Admin and Accountant).

### Phase 5: Academic Records (Attendance & Results)
- [ ] **Attendance System:**
  - [ ] **Admin:** View all attendance records across all years.
  - [ ] **Teacher:** View and mark attendance ONLY for allocated students in the active session/term.
  - [ ] Interface to mark `Present` or `Absent`.
  - [ ] For `Absent`, require a reason and display student parent contact info for quick access.
- [ ] **Result & Grading System:**
  - [ ] **Teacher Interface:** Select assigned class -> open editable spreadsheet-like table.
  - [ ] Table fields: `SN`, `Admission Number`, `Name`, `CA1`, `CA2`, `Exam`, `Total` (auto-calculated), `Grade` (auto-calculated).
  - [ ] **AJAX Auto-save:** Implement auto-saving on input blur/change to prevent data loss.
  - [ ] **Result Processing:** Button to process entered scores and generate a preview of the report sheet.
  - [ ] Restriction: Teachers can only view/edit current active session/term results. Admins/Students can view historical records.

### Phase 6: Review & Finalization
- [ ] Thoroughly test RBAC to ensure Teachers and Accountants cannot access unauthorized areas.
- [ ] Test complex fee logic (NI vs OS, Gender-specific fees).
- [ ] Verify AJAX autosave integrity under network latency.
- [ ] Final UI/UX polish across portals.