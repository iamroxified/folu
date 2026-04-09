# Admin Module Requirements

This document captures the requested business rules and feature updates for the school management admin area after the Laravel conversion.

## Current Context

- Admin pages are rendered from `resources/views/admin/pages`.
- Shared admin layout files live in `resources/views/admin/partials`.
- Admin routes are currently served through Laravel and mapped from `/admin/*.php` URLs.

## Implementation Status

Status as of 2026-04-05:

- Student registration now captures parent or guardian contact details.
- Active session and term selection is available and shown across the admin header.
- Fee allocation helpers support assigning fees to students by active session and class.
- Teacher assignment now supports multiple classes and multiple subjects in the admin assignment screen.
- A teacher portal now exists with login, assignment-aware student views, attendance entry, score entry, announcements, and password change.
- A student portal now exists with login, payment history, printable receipts, attendance, announcements, password change, and fee-gated results.
- Nigerian grading rules are enforced automatically during score entry.
- Attendance entry for both admin and teacher portals now uses a bulk table workflow with class, optional subject, date, session, and remarks.
- Class management now uses the current session-aware class schema with class arm, class level, capacity, and form teacher support.
- Subject management now uses the live `subject_name`, `subject_code`, `class_level`, and `is_core` schema.
- Class subject assignment and timetable management now run on Laravel-backed tables instead of the missing legacy tables.

Remaining note:

- The portals and school-core admin workflows covered here are functional, but the codebase still contains older unrelated legacy admin pages outside this school-management scope. Those can be modernized further in later refactor passes.

## 1. Student Registration

When creating a new student, the registration form must collect parent or guardian contact details in addition to the student record.

Required parent or guardian fields:

- Full name
- Phone number
- Email address

These details should be stored so the school can contact parents for attendance, fee issues, and school communication.

## 2. Active Session and Term

The system must provide a way to set the active academic session and active term.

Expected behavior:

- Only one academic session should be active at a time.
- Only one term should be active at a time for the selected session.
- Fees, attendance, grading, and result processing should use the active session and term by default.
- The current session and term should be visible to admins on relevant pages.

## 3. Fee Assignment by Session and Class

At the beginning of a session or term, fees should be assignable to all students in a class automatically.

Example:

- If the active period is `2025/2026` and `1st Term`
- And fees are configured for `JSS1A`
- Then all students currently in `JSS1A` for that session and term should receive those fee entries

Required workflow:

- Add an `Assign Fees` action where session or fee allocation is managed
- Assign fees in bulk to students based on class and active term
- Prevent duplicate fee allocation for the same student, session, term, and fee item

## 4. Teacher Assignment Rules

The system must support both primary and secondary school teaching structures.

Required rules:

- A teacher can be a class teacher in the primary section
- A teacher can also be a subject teacher in the secondary section
- A teacher can be assigned to multiple classes
- A teacher can be assigned to multiple subjects
- A teacher can teach across levels where needed

Example:

- A teacher may be assigned to `Primary 1`
- The same teacher may also teach `Basic Science` for `JSS1`, `JSS2`, and `JSS3`
- The same teacher may also teach `English` for `SS1` to `SS3`

This means the system should not restrict a teacher to a single class or a single subject.

## 5. Teacher Role

Create a dedicated teacher role and teacher login flow.

Teachers should be able to:

- Log in with teacher credentials
- View classes and subjects assigned to them
- View students attached to those assignments
- Mark attendance
- Enter subject scores
- Perform teacher-related school actions from their own portal

Teacher actions should be limited to their assigned classes and subjects.

## 6. Student Role

Create a dedicated student role and student login flow.

Expected login behavior:

- Username should be the student's admission number
- Default password should be `password`
- The system should support changing the password later

Students should be able to:

- View payment history
- Print fee receipts
- View scores and results
- View attendance
- View announcements
- Access other student-facing school information

Restriction:

- A student must not be able to view results if all required fees for the relevant period have not been fully paid

## 7. Grading System

Grades should follow the standard Nigerian secondary school grading scale.

Recommended grading table:

- `70-100` = `A1`
- `60-69` = `B2`
- `50-59` = `B3`
- `45-49` = `C4`
- `40-44` = `C5`
- `35-39` = `C6`
- `30-34` = `D7`
- `25-29` = `E8`
- `0-24` = `F9`

Implementation rule:

- Every student score should automatically produce a grade
- Where a simplified display is needed, `B2` and `B3` can both be treated as `B`
- Example: a score between `50` and `59` should show `B3`

## 8. Attendance Workflow

Attendance must work for both admins and teachers.

Required workflow:

- The teacher or admin selects the class
- The teacher also selects the subject where subject-level attendance is required
- Attendance should be entered on a table view
- Multiple students should be markable at once
- Checkbox or bulk-friendly controls should be used for fast entry
- Attendance should be saved against the correct date, class, subject, session, and term

This workflow should support daily attendance entry without requiring each student to be marked one by one on separate forms.

## 9. Implementation Notes

The items in this file are requirements and should guide the next refactor phases for:

- Admin workflows
- Teacher portal
- Student portal
- Session management
- Fee allocation
- Attendance and grading

As development continues, this file should be kept in sync with what has actually been implemented.
