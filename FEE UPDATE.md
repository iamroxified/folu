# FOLU INTERNATIONAL SCHOOLS

## STUDENT REGISTRATION & SCHOOL FEES SYSTEM — UPDATED SYSTEM FLOW

The system should be redesigned so that student registration, student status, fee structure, fee allocation, payments, outstanding balances and additional charges all work together automatically.

The system must distinguish between:

* New Intake
* Returning Student
* Male
* Female
* Class
* Academic Session
* Term
* New Intake Fee Structure
* Returning Student Fee Structure
* Additional/Extra Fees
* Amount Paid
* Amount Owed
* Payment History

---

# 1. STUDENT MODE OF ENTRY

The system should support three methods of adding students.

## A. Student Self-Registration

A new student/parent can register through the portal.

The registration form should capture relevant information such as:

* Student ID/Application Number
* First Name
* Middle Name
* Last Name
* Gender
* Date of Birth
* Class
* Academic Session
* Student Type
* Parent/Guardian Information
* Contact Information
* Address
* Previous School, where applicable
* Other required admission information

The student should be classified as:

### NEW INTAKE

A student joining the school for the first time.

OR

### RETURNING STUDENT

A student who was already enrolled in the school and is continuing into another term/session.

Self-registration should initially create the student/application record for admin review where necessary.

---

# 2. ADMIN STUDENT ENTRY

The administrator should be able to manually create a student.

During manual registration, the administrator must select:

### Student Type

* New Intake
* Returning Student

The administrator should also select:

* Academic Session
* Class
* Gender

The system should use these selections to determine the applicable fee structure.

---

# 3. EXCEL STUDENT UPLOAD

The administrator should be able to upload students in bulk using Excel.

The Excel template should contain the required fields, for example:

| Field                 | Required                      |
| --------------------- | ----------------------------- |
| Student ID            | Yes/Auto                      |
| First Name            | Yes                           |
| Middle Name           | No                            |
| Last Name             | Yes                           |
| Gender                | Yes                           |
| Class                 | Yes                           |
| Student Type          | Yes                           |
| Academic Session      | Yes                           |
| Parent Name           | Yes                           |
| Parent Phone          | Yes                           |
| Other required fields | Based on system configuration |

The upload system must validate:

* Required fields
* Valid gender
* Valid class
* Valid student type
* Valid academic session
* Duplicate students
* Duplicate student IDs
* Invalid data

The administrator should see an import summary:

**Total Records:** 100
**Successfully Imported:** 96
**Failed:** 4

The failed records should be downloadable with the reason for failure.

---

# 4. STUDENT STATUS

Every student must have a clearly defined status.

### Student Type

```text
NEW_INTAKE
RETURNING
```

This value is critical because it determines how the student's school fees are calculated.

Do not rely solely on the student's current class or session to determine whether they are new or returning.

---

# 5. ACADEMIC SESSION

The system should have an academic session structure.

Example:

```text
2026/2027
2027/2028
2028/2029
```

An administrator should be able to create a new academic session without modifying the source code.

Each session should have:

* Session name
* Start date
* End date
* Status

  * Active
  * Closed

---

# 6. TERMS

Each academic session should contain:

* First Term
* Second Term
* Third Term

The administrator should be able to configure the active term.

Example:

```text
Academic Session: 2026/2027
Current Term: First Term
```

The system must know which term is currently active.

---

# 7. FEE STRUCTURE

The fee structure is the most important part of the redesign.

The system must allow administrators to create fees based on:

1. Academic Session
2. Class
3. Gender
4. Student Type
5. Term, where applicable
6. Fee Category

The system must distinguish between:

### NEW INTAKE FEES

and

### RETURNING STUDENT FEES

---

# 8. NEW INTAKE FEE STRUCTURE

New intake fees are **session-based and NOT term-based**.

This means that when a student joins the school as a new intake, the system should charge the applicable new-intake fee for that class, gender and academic session regardless of whether the student joins in First, Second or Third Term.

Example:

```text
Session: 2026/2027
Class: Primary 3
Gender: Male
Student Type: New Intake

New Intake Fee: ₦250,000
```

If the student joins during Second Term:

```text
New Intake Fee = ₦250,000
```

If the student joins during Third Term:

```text
New Intake Fee = ₦250,000
```

The term does NOT change the new-intake fee.

---

# 9. NEW INTAKE FEES BY GENDER

The system must support different new-intake fees for male and female students.

Example:

```text
Primary 3
2026/2027

New Intake - Male: ₦250,000
New Intake - Female: ₦245,000
```

The system should automatically select the correct fee based on the student's gender.

The administrator should not have to manually calculate this.

---

# 10. RETURNING STUDENT FEE STRUCTURE

Returning students are charged according to:

* Academic Session
* Class
* Gender
* Term
* Student Type

Example:

```text
Session: 2026/2027
Class: Primary 3
Gender: Male
Student Type: Returning
Term: First Term

Fee: ₦150,000
```

Second Term may have a different amount:

```text
Second Term: ₦140,000
```

Third Term:

```text
Third Term: ₦135,000
```

The system should allow every term to have a separate fee.

---

# 11. RETURNING STUDENT PROMOTION

The system should understand that students move from one class to another when a new session begins.

Example:

### 2025/2026

Student:

```text
Primary 3
Male
Returning
```

At the beginning of:

### 2026/2027

The student becomes:

```text
Primary 4
Male
Returning
```

The system should NOT treat the student as a new intake merely because his class changed.

The student's historical record should remain intact.

---

# 12. STUDENT FEE ALLOCATION

When creating a payment/fee record for a student, the system should automatically determine the applicable fee using this logic:

```text
IF Student Type = NEW_INTAKE

    Find fee where:
        Session = Student Session
        Class = Student Class
        Gender = Student Gender
        Student Type = New Intake

    Ignore Term

ELSE IF Student Type = RETURNING

    Find fee where:
        Session = Student Session
        Class = Student Class
        Gender = Student Gender
        Student Type = Returning
        Term = Selected Term
```

This should be handled automatically by the system.

---

# 13. LUMP-SUM FEE

The system should calculate the student's total applicable school fee.

For example:

```text
Tuition                         ₦100,000
Books                            ₦20,000
Uniform                          ₦25,000
Development Levy                ₦30,000
ICT                              ₦15,000
----------------------------------------
TOTAL                            ₦190,000
```

The system should support either:

### A. One lump-sum fee

or

### B. Itemized fee structure

The recommended approach is to store the individual fee components while displaying the total amount as the student's payable amount.

This gives the school better reporting and flexibility.

---

# 14. FEE CATEGORIES

The administrator should be able to create fee categories.

Examples:

* Tuition
* Registration
* Books
* Uniform
* Examination
* ICT
* Development Levy
* Medical
* Transportation
* Feeding
* Extra-curricular
* Other

Each fee category should be configurable.

---

# 15. STUDENT PAYMENT RECORD

Every student should have a financial profile.

Example:

### Student

**John Doe**

Class: Primary 4
Gender: Male
Student Type: Returning
Session: 2026/2027
Term: First Term

### Financial Summary

```text
Total Fee        ₦180,000
Additional Fees   ₦20,000
-------------------------
Total Payable    ₦200,000

Amount Paid      ₦120,000
Amount Owed       ₦80,000
```

---

# 16. ADDITIONAL / EXTRA FEES

The system must allow administrators to add an additional charge to an individual student's account after the original fee has been generated.

Example:

Student's original fee:

```text
₦180,000
```

An additional charge is later added:

```text
Extra Examination Fee = ₦10,000
```

The system should automatically update:

```text
Original Fee        ₦180,000
Additional Fee       ₦10,000
-----------------------------
Total Payable       ₦190,000
```

If the student had already paid ₦100,000:

```text
Total Payable       ₦190,000
Amount Paid         ₦100,000
Amount Owed          ₦90,000
```

The extra fee must NOT overwrite the original fee.

It must be recorded as a separate transaction/charge.

---

# 17. EXTRA FEE DETAILS

Whenever an administrator adds an extra fee, capture:

* Fee name
* Amount
* Reason/description
* Date added
* Added by
* Session
* Term, where applicable
* Payment status

Example:

```text
Fee: Examination Fee
Amount: ₦10,000
Reason: External Examination
Added By: Admin
Date: 10/10/2026
```

This creates an audit trail.

---

# 18. PAYMENT HISTORY

Every payment must be recorded separately.

Example:

### Payment 1

₦50,000
10/09/2026

### Payment 2

₦30,000
20/09/2026

### Payment 3

₦40,000
02/10/2026

Total:

```text
₦120,000
```

Never overwrite previous payments.

---

# 19. PAYMENT STATUS

The system should automatically determine:

### PAID

When:

```text
Amount Paid >= Total Payable
```

### PARTIALLY PAID

When:

```text
Amount Paid > 0
AND
Amount Paid < Total Payable
```

### UNPAID

When:

```text
Amount Paid = 0
```

### OVERPAID

When:

```text
Amount Paid > Total Payable
```

If overpayment is allowed, the system should record the credit balance.

---

# 20. AMOUNT OWED CALCULATION

The system should calculate:

```text
Amount Owed =
Total Payable - Amount Paid
```

Where:

```text
Total Payable =
Base Fee + Additional Fees
```

Example:

```text
Base Fee              ₦150,000
Additional Fees        ₦20,000
------------------------------
Total Payable         ₦170,000

Amount Paid           ₦100,000

Amount Owed            ₦70,000
```

---

# 21. FEE STRUCTURE ADMINISTRATION

Create an admin interface:

## Fee Structure

The administrator should be able to:

* Create fee structure
* Edit fee structure
* Activate/deactivate fee structure
* Duplicate a previous session's fee structure
* Set new intake fees
* Set returning student fees
* Set male fees
* Set female fees
* Set term-specific fees
* Add fee categories
* View fee history

Example:

```text
Academic Session: 2026/2027
Class: Primary 4

NEW INTAKE
Male:       ₦250,000
Female:     ₦245,000

RETURNING

First Term
Male:       ₦150,000
Female:     ₦145,000

Second Term
Male:       ₦140,000
Female:     ₦135,000

Third Term
Male:       ₦135,000
Female:     ₦130,000
```

---

# 22. PREVENT DUPLICATE FEE STRUCTURES

The system should prevent an administrator from accidentally creating two active fee structures for the exact same combination.

For example, it should not allow two active records for:

```text
2026/2027
Primary 4
Male
Returning
First Term
```

There should only be one active configuration for that combination.

---

# 23. FEE STRUCTURE VALIDATION

Before activating a fee structure, validate that:

* Session exists
* Class exists
* Gender is valid
* Student type is valid
* Required term is selected for returning students
* Term is NOT required for new intake
* Amount is valid
* Duplicate active structure does not exist

---

# 24. STUDENT PROMOTION / NEW SESSION

At the end of an academic session, the system should provide a promotion process.

Example:

```text
2025/2026
Primary 3
        ↓
2026/2027
Primary 4
```

The administrator should be able to:

* Promote students individually
* Promote an entire class
* Promote selected students
* Repeat a student
* Withdraw a student
* Graduate a student
* Transfer a student

A promoted student remains a returning student.

---

# 25. NEW INTAKE IDENTIFICATION

A student should only be classified as:

### NEW INTAKE

if the student is genuinely joining the school for the first time.

The system should warn the administrator if an existing student is being registered as a new intake.

Example:

> **Possible Existing Student Found**
>
> A student with similar details already exists in the system.
>
> Please confirm whether this is:
>
> **Existing Student / New Intake**

This helps prevent duplicate student accounts.

---

# 26. STUDENT FINANCIAL HISTORY

Each student's profile should have:

### Academic History

```text
2024/2025 - Primary 2
2025/2026 - Primary 3
2026/2027 - Primary 4
```

### Financial History

```text
2024/2025
First Term
Amount Paid: ₦XXX

Second Term
Amount Paid: ₦XXX

Third Term
Amount Paid: ₦XXX

2025/2026
...
```

This makes the student's financial history traceable across sessions.

---

# 27. DASHBOARD

The administrator dashboard should provide financial summaries.

Example:

### CURRENT SESSION

**Total Students:** 250

**New Intake:** 45

**Returning:** 205

**Total Expected Fees:** ₦XX,XXX,XXX

**Total Collected:** ₦XX,XXX,XXX

**Outstanding:** ₦XX,XXX,XXX

**Fully Paid:** 120

**Partially Paid:** 85

**Unpaid:** 45

---

# 28. PAYMENT REPORTS

Allow administrators to generate:

* Daily payment report
* Weekly payment report
* Monthly payment report
* Term payment report
* Session payment report
* Class payment report
* Gender payment report
* New intake payment report
* Returning student payment report
* Outstanding fees report
* Student payment history
* Additional fee report

Reports should be exportable to Excel/PDF where appropriate.

---

# 29. IMPORTANT BUSINESS RULE

The system must NEVER assume that every student pays the same fee.

The payable fee is determined by:

```text
STUDENT
   ↓
Student Type
   ↓
Gender
   ↓
Class
   ↓
Academic Session
   ↓
IF NEW INTAKE → Session/Class/Gender Fee
   ↓
IF RETURNING → Session/Class/Gender/Term Fee
```

---

# 30. RECOMMENDED DATABASE CONCEPT

Do not store the student's total payable amount as the only source of truth.

Use separate records for:

### Students

Student identity and admission information.

### Academic Enrollments

Student + session + class + term/status.

### Fee Structures

The school's configured fees.

### Student Fee Allocations

The actual fee assigned to a particular student.

### Additional Charges

Extra fees added to a specific student.

### Payments

Individual payments made by the student.

This separation is important because the school's fee structure can change while historical student allocations and payments must remain intact.

---

# 31. FINANCIAL CALCULATION

The system should use:

```text
BASE FEE
   +
ADDITIONAL CHARGES
   -
PAYMENTS
   =
OUTSTANDING BALANCE
```

Example:

```text
Base Fee              ₦200,000
Additional Charges     ₦15,000
------------------------------
Total Payable         ₦215,000

Payments:
₦50,000
₦50,000
₦40,000
------------------------------
Total Paid            ₦140,000

Outstanding            ₦75,000
```

---

# 32. HISTORICAL DATA PROTECTION

Once a student has been allocated a fee and payments have been made, changing the general fee structure must NOT automatically change the student's historical financial record.

For example:

If Primary 4 returning students were originally charged:

```text
₦150,000
```

and management later changes the fee structure to:

```text
₦170,000
```

existing allocated student records should remain based on the fee that was actually assigned unless an administrator deliberately adjusts the student's allocation.

This is essential for financial integrity.

---

# 33. AUDIT LOG

Important financial actions should be logged.

Record:

* Who created the fee
* Who changed the fee
* Who added an extra charge
* Who recorded a payment
* Who edited a payment
* Who cancelled/reversed a payment
* Date/time of action
* Previous value
* New value

Financial records should never be silently modified.

---

# 34. FINAL USER EXPERIENCE

The administrator should experience the system like this:

### NEW STUDENT

```text
Register Student
      ↓
Select: NEW INTAKE
      ↓
Select Session
      ↓
Select Class
      ↓
Select Gender
      ↓
System automatically finds New Intake Fee
      ↓
Generate Student Fee
      ↓
Record Payment
      ↓
Balance Automatically Calculated
```

### RETURNING STUDENT

```text
Select Existing Student
      ↓
Select New Session
      ↓
Select Class
      ↓
Select Gender
      ↓
Select Term
      ↓
System automatically finds Returning Student Fee
      ↓
Generate Student Fee
      ↓
Record Payment
      ↓
Balance Automatically Calculated
```

### EXTRA FEE

```text
Open Student Financial Profile
      ↓
Add Additional Fee
      ↓
Enter Fee + Amount + Reason
      ↓
System Updates Total Payable
      ↓
Outstanding Balance Automatically Recalculated
```

---

# 35. CRITICAL IMPLEMENTATION RULE

Before modifying the application:

1. Inspect the current database structure.
2. Inspect existing student tables/models.
3. Inspect current fee/payment tables.
4. Inspect existing registration flow.
5. Inspect existing payment flow.
6. Identify what can be reused.
7. Identify what needs migration.
8. Do not destroy existing financial records.
9. Create migrations for the new structure.
10. Preserve historical payments.
11. Implement the new fee allocation engine.
12. Test New Intake.
13. Test Returning Student.
14. Test male/female fee differences.
15. Test all three terms.
16. Test new-intake admission in Second/Third Term.
17. Test student promotion.
18. Test additional fees.
19. Test partial payments.
20. Test full payments.
21. Test overpayments if supported.
22. Test Excel imports.
23. Test duplicate students.
24. Test reports.
25. Test permissions and audit logs.

The final system should make fee calculation **automatic, traceable and resistant to human error**.

The administrator should not have to manually calculate which fee a student should pay. Once the administrator identifies the student's **session, class, gender and student type**, and for returning students the **term**, the system should determine the correct fee automatically.
