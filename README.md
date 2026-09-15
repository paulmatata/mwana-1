# Mwana — School Management Platform

Built by CLOUDP TECH. Multi-school platform: Super Admin → Principal → Teacher → Parent.

## What's in this project so far (Phases 1–6, all complete)

This is **not** a full Laravel install (no `vendor/`, no framework bootstrap files like
`artisan`'s internals) — I can't run `composer install` on my end since I don't have
internet access. Instead, this zip is an **overlay**: all the Mwana-specific code,
ready to drop into a fresh Laravel 11 skeleton.

```
app/Http/Controllers/HomeController.php               Public landing page controller
app/Http/Controllers/Controller.php                    Base controller
app/Http/Controllers/Auth/LoginController.php           Staff/parent login (email or phone + password)
app/Http/Controllers/SuperAdmin/DashboardController.php Super Admin overview/stats
app/Http/Controllers/SuperAdmin/SchoolController.php     Create/edit/view/suspend schools
app/Http/Controllers/SuperAdmin/PrincipalController.php  Create principal accounts, reset password, suspend
app/Http/Controllers/Principal/                          Dashboard, classes, subjects, teachers, subject-
                                                          teacher assignments, timetable builder, exam
                                                          creation + approval workflow
app/Http/Controllers/Teacher/                             Dashboard, student management, Excel marks
                                                          upload/download, timetable view
app/Http/Controllers/ParentPortal/                        Guided first-login (school→class→admission#+name),
                                                          dashboard, results, fees, notices
app/Http/Requests/Principal/                              Form validation for all of the above, all school-scoped
app/Http/Requests/Teacher/                                Form validation for student + marks upload
app/Http/Requests/ParentPortal/                           Validation for the student-lookup step and account creation
app/Http/Requests/SuperAdmin/                            Form validation for schools + principals
app/Support/GradeCalculator.php                           Converts a numeric score into a letter grade
                                                          (Phase 7 adds bulk import endpoints to the Teacher
                                                          student controller, Principal teacher controller,
                                                          and a bulkStore on Principal timetable controller -
                                                          same files, not separately listed here)
app/Http/Middleware/RoleMiddleware.php                   Restricts routes by role (role:principal, etc.)
app/Models/                                              User, School, SchoolClass, Subject,
                                                          ClassSubjectTeacher, Student, Exam, Mark,
                                                          FeeRecord, Timetable, Notice
database/migrations/                                     Full schema for Phase 1
database/seeders/                                        Creates the first Super Admin account
resources/views/home/index.blade.php                     Public landing page (explains Mwana + parent flow)
resources/views/layouts/app.blade.php                    Shared public layout (Mwana color palette)
resources/views/layouts/dashboard.blade.php              Shared authenticated dashboard shell (sidebar/nav)
resources/views/auth/login.blade.php                     Login page
resources/views/super-admin/                             Dashboard + school/principal management views
resources/views/principal/                                Dashboard, classes, subjects, teachers, assignments,
                                                          timetable builder, exams (create + approval screen)
resources/views/teacher/                                  Dashboard, student management, marks upload/review,
                                                          timetable view
resources/views/parent/first-login/                        The 4-step guided wizard (school → class →
                                                          admission#+name → create account)
resources/views/parent/                                    Dashboard (child cards), results, fees, notices
routes/web.php                                            Auth + Super Admin routes wired; Principal/Teacher/
                                                          Parent groups stubbed for Phases 3–5
bootstrap/app.php                                        Reference: how to register the `role` middleware alias
composer.json                                             Dependencies (Laravel 11, Sanctum, PhpSpreadsheet)
.env.example                                               Environment template
```

**Important:** when you run `composer create-project laravel/laravel:^11.0 mwana`, Laravel already
creates a few of its own default migrations (users, cache, jobs/sessions tables, etc.) with earlier
timestamps than ours. **Don't delete those** — just drop our migration files in alongside them. Our
`2024_01_01_000001_create_users_table.php` is deliberately named to run right after Laravel's own
default files but before anything else, so schools/classes/students etc. all migrate cleanly after it.
If Laravel's default `users` migration conflicts with ours (same table, different columns), delete
**Laravel's default one only** (usually `0001_01_01_000000_create_users_table.php`) since ours replaces
it with the role-based version Mwana needs.

## Setup steps

1. **Create a fresh Laravel 11 project** (this pulls in `vendor/`, `artisan`, and all
   the framework scaffolding I can't ship you directly):
   ```bash
   composer create-project laravel/laravel:^11.0 mwana
   cd mwana
   ```

2. **Copy this zip's contents into that new project**, overwriting where prompted:
   - `app/Http/Controllers/`, `app/Http/Middleware/`, `app/Models/`
   - `database/migrations/`, `database/seeders/`
   - `resources/views/`
   - `routes/web.php`
   - `bootstrap/app.php`
   - `composer.json` (or manually merge the `require` block into your existing one)
   - `.env.example`

3. **Install the added dependencies:**
   ```bash
   composer update
   ```
   (Pulls in `laravel/sanctum` and `phpoffice/phpspreadsheet`, used from Phase 4 onward
   for the marks-upload Excel sheet.)

4. **Set up your `.env`:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Then edit `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` to match your local MySQL setup.

5. **Run migrations and seed the Super Admin:**
   ```bash
   php artisan migrate --seed
   ```
   This creates the platform owner account:
   - Email: `admin@mwana.app`
   - Password: `ChangeMe123!`

   **Change this password immediately** once login is built in Phase 5 — for now it's
   just sitting in the database ready to be used.

6. **Serve it locally:**
   ```bash
   php artisan serve
   ```
   Visit `http://localhost:8000` — you should see the Mwana landing page explaining
   the platform and the parent login flow.

## Database design notes

- **Multi-tenancy is row-level**, not separate databases: every school-owned table
  (`students`, `school_classes`, `subjects`, `marks`, `fee_records`, `timetables`,
  `notices`) carries a `school_id`. All future controllers must scope queries by the
  logged-in user's `school_id` — this is the single most important rule to enforce
  consistently, especially for teachers and principals.
- **Admission numbers are unique per school**, not globally (`students` table has a
  composite unique index on `school_id + admission_no`). Two different schools can
  both have a student "ADM001".
- **Results visibility is gated at the `exams` level, not per mark.** A teacher
  uploads marks against an `exam` row (e.g. "Term 2 End-Term Exam 2026"). The principal
  approves the whole exam. Only marks belonging to an `approved` exam are visible to
  parents (`Student::approvedMarks()`).
- **Fee records are informational only.** There is intentionally no payment
  processing anywhere in this schema — `fee_records` just stores what the school
  has recorded (amount due, amount paid, due date, notes).
- **The parent ↔ student link** (`parent_student` pivot) is created the first time a
  parent successfully completes the guided school → class → admission number + name
  lookup. This is what Phase 5 will build.

## Trying out Phase 2

1. Log in at `/login` with the seeded Super Admin (`admin@mwana.app` / `ChangeMe123!`).
2. You'll land on `/super-admin/dashboard`.
3. Add a school, then open it and create a principal account. The temporary password
   is shown once on screen — copy it before navigating away.
4. Try suspending the school: any principal/teacher/parent tied to it will be blocked
   at login until it's reactivated.

Principal login itself (the actual Principal dashboard) is built in Phase 3 — for now,
a principal account can be created but has nowhere to log in to yet.

## Trying out Phase 3

1. Log in as the principal account you created in Phase 2 (use the temporary password
   that was shown on screen — Phase 3 doesn't change how principals log in, they use
   the same `/login` form as everyone else).
2. You'll land on `/principal/dashboard`.
3. Add a class, a subject, and a teacher account (same auto-generated-password pattern
   as Super Admin creating principals).
4. Go to **Assignments** and link a teacher to a subject within a class — this is what
   will scope their marks-upload access in Phase 4.
5. Create an exam window under **Exams & Results** for a class.

Note: since marks upload is Phase 4, there's no UI yet for a teacher to actually enter
scores. To test the approval screen end-to-end before Phase 4 exists, you can manually
insert a couple of rows into the `marks` table via `php artisan tinker` or your DB
client, then visit the exam's review page and try Submit → Approve/Reject.

## Trying out Phase 4

1. As the **principal**, go to **Timetable** and add a slot for one of your teacher
   assignments (pick a class first from the dropdown at the top).
2. Log out, log in as the **teacher** account whose assignment you just scheduled.
3. Check **My Timetable** — the slot you just added should appear under the right day.
4. Go to **Marks Upload**, pick the exam + subject combination, and download the
   template — it'll come pre-filled with admission numbers and names for that class.
5. Fill in a few scores in the downloaded spreadsheet, save it, and upload it back.
   You should see the scores (and auto-calculated letter grades) appear in the review
   table on the right, and any problem rows (e.g. a typo'd admission number) get
   reported back rather than silently skipped.
6. Back on that same teacher marks page, click **Submit for review** — the exam moves
   to "submitted" status and can no longer be edited by the teacher.
7. Log back in as the **principal**, open **Exams & Results**, review the exam, and
   **Approve** it (or reject with a reason, which sends it back to the teacher to fix).

## Trying out Phase 5

1. Log out of any staff account, and visit `/parent/first-login` (also linked from
   `/login`'s "Parent logging in for the first time?" and from the homepage).
2. Walk through: school → class → admission number + full name (use a student you
   registered as a teacher in Phase 4) → set a password.
3. You'll land on the parent dashboard showing that child as a card, with buttons for
   Results, Fees, and Notices.
4. **Results** only shows exams the principal has approved — try it before and after
   approving an exam in Phase 3/4's flow to see the difference.
5. **Fees** only shows what's been recorded — there's no payment button anywhere, by
   design. To see data here, insert a row into `fee_records` manually (no UI exists
   yet for staff to enter fee records — see note below).
6. **Notices** — same story, no staff-side UI to post one yet; insert a row into
   `notices` manually to test the parent-facing display.
7. From the dashboard, try **+ Add another child** — same guided flow, but since
   you're already logged in, it skips straight to linking (no second password needed).

**Update (Phase 8):** the staff-side UI for fee records and notices mentioned as a gap
below is now built — see the Phase 8 section further down.

## Trying out Phase 7 (bulk operations)

1. **Bulk student import** — log in as a teacher, go to **Students → Bulk add**, pick a
   class, download the template, fill in a few rows, upload it back. Duplicate
   admission numbers (whether already in the system or repeated in the file) are
   skipped and listed, not overwritten.
2. **Bulk teacher creation** — log in as a principal, go to **Teachers → Bulk add via
   spreadsheet**, download the template, fill in a few names + emails/phones, upload.
   You'll land back on the Teachers page with a table of every account created and its
   password, shown once.
3. **Bulk timetable entry** — go to **Timetable**, pick a class. The "Add slots" panel
   now starts with 3 blank rows and a "+ Add another row" button (plain JS, no build
   step needed). Fill in a few, hit **Save all slots** — valid rows save, and anything
   that clashes (either with an existing slot, or with another row in the same batch)
   is reported back by row number instead of silently failing or blocking the rest.

## Trying out Phase 8 (fees & notices)

1. As **principal**, go to **Fees → Bulk post to a class**, pick a class (or leave
   blank for the whole school), set an amount due and due date, submit. Check the
   **Fees** list — one record per active student.
2. Edit one of those records and update "Amount paid" as if a payment came in at the
   school — the balance recalculates automatically.
3. Go to **Notices → Post a notice**, try all three targets (whole school / one class
   / one student).
4. As a **teacher**, go to **Notices → Post a notice** — notice there's no "whole
   school" option here, only class or student, and you'll only ever see notices you
   posted yourself on the index page (not the principal's or other teachers').
5. Log in as the **parent** linked to a student in that class — their **Fees** page
   should show the bulk-posted record, and **Notices** should show anything targeted
   at their child, their child's class, or the whole school.

## Trying out Phase 9 (year-end promotion)

1. As **principal**, edit an existing class and set **"Promotes to"** — pick another
   class in your school (e.g. Grade 5 Blue → Grade 6 Blue). Leave it as "graduating
   class" for whichever class is your school's final grade.
2. Back on the **Classes** page, you'll now see each class's promotion destination in
   a new column, and a **Promote** button per row.
3. Click **Promote** on a class that has students — you'll land on a review screen
   with every active student pre-checked. Uncheck anyone who should repeat the class
   (held back), then submit.
4. Checked students move to the destination class immediately; unchecked students stay
   exactly where they are. If the class had no destination set, checked students get
   marked **graduated** instead — try this on a "final grade" class to see the
   different confirmation wording.
5. Nothing about a promoted student's **historical results** changes — their approved
   exam results stay tied to the class they were in at the time, so results history
   survives being promoted (or graduating) without any special handling needed.

**Design note:** promotion is always manual and per-class — there's no scheduled job
silently moving students overnight. You review the list, uncheck exceptions, then
commit. Running it again on an already-promoted (now empty) class is harmless, since
there's nothing left in "active" status to select.

## Trying out Phase 10 (class-teacher-only submission + missing-marks tracker + multi-class exams)

1. As **principal**, go to **Classes** and make sure at least one class has a class
   teacher assigned (needed for the rest of this to work).
2. Go to **Exams & Results → New exam window**. Notice the class picker is now a
   checklist with a "whole school" toggle — select several classes (or the whole
   school) and submit once. You'll get one exam per class, and any class missing a
   class teacher is skipped with a clear note (rather than creating an exam nobody
   can ever submit).
3. As a **teacher assigned to a subject but not the class teacher**, open a subject's
   marks page — you'll see a note that submission isn't done from here anymore, with
   a pointer to whoever the actual class teacher is.
4. As the **class teacher**, your dashboard now shows a "You're the class teacher for"
   card. Click through to **Class Exams (as Class Teacher)** — this is the missing-marks
   tracker: one row per subject assigned to the class, showing marks entered vs. total
   students, and a clear Complete/Missing status per subject.
5. Try submitting while a subject is still missing marks — it's allowed (your call as
   class teacher), but the page warns you first and the confirmation dialog reflects
   whether everything's complete.
6. Try hitting a class exam's overview page as a teacher who *isn't* that class's class
   teacher (e.g. by guessing the URL) — you'll get a 403, not the tracker.

**Design note:** submission authority now lives entirely with the class teacher (or the
principal, as an override). The old per-subject "submit" button is gone — a subject
teacher can still upload/review their own subject's marks, they just can't be the one
who sends the whole class's results forward.

## Trying out Phase 11 (electives, teacher qualifications, results totals)

**New migrations this time** — run `php artisan migrate` after pulling these in:
- `add_is_core_to_subjects_table` — every existing subject defaults to core (`is_core = true`), so nothing about current behavior changes until you deliberately mark something as an elective.
- `create_student_subject_table` — the enrollment pivot.
- `create_teacher_subject_table` — the qualifications pivot.
- `backfill_student_subject_for_existing_data` — **important**: without this, every class you already set up before Phase 11 would suddenly show an empty marks-upload roster, since the enrollment table didn't exist yet when those students/assignments were created. This migration auto-enrolls every existing active student into every core subject already assigned to their class, so nothing breaks.

**Electives:**
1. As **principal**, edit a subject (or create a new one) and uncheck "Core subject" to make it an elective.
2. Assign a teacher to that elective subject via **Assignments** as usual — notice the flash message now tells you it's an elective and needs enrollment set up.
3. Log in as that **teacher**, go to **Elective Enrollment**, click **Manage enrollment** on the subject, and check off which students in the class actually take it.
4. Go to **Marks Upload** for that subject — the downloaded template now only lists the enrolled students, not the whole class. A student who doesn't take the subject simply never appears — no "missing mark" ever gets attributed to them, and their parent never sees that subject on the results page at all.
5. If you try uploading a row for a real class member who isn't enrolled, it's reported back specifically as "isn't enrolled in this subject," not a generic "not found" — so you can tell the difference between a typo and a student who legitimately doesn't take it.
6. Check the **Class Exams (as Class Teacher)** missing-marks tracker — an elective's "total" now correctly reflects only its enrolled students, not the whole class.

**Teacher-subject qualifications:**
1. As **principal**, create a teacher (or edit an existing one) and check off which subjects they're qualified to teach.
2. Go to **Assignments** — pick a subject, and watch the teacher dropdown narrow to only qualified teachers. Teachers with no qualifications set at all still show up for everything (permissive fallback, so nothing gets locked out for schools mid-setup).

**Results totals:**
- A parent's **Results** page now shows a Total/Average/Mean Grade row under each exam's subject breakdown.
- The principal's exam review screen shows the same three columns per student, so you can sanity-check before approving.

**Design note on year-to-year electives:** enrollment isn't tied to a specific academic year in the schema — it's just "is this student currently enrolled in this subject." When a class's elective assignment gets recreated for a new year, whatever was enrolled last time shows up pre-checked on the enrollment screen (a reasonable default), but it's always editable, so nothing is locked in silently.

## Trying out Phase 12 (timetable rebuild, draft/publish, client-side validation)

**New migration** — run `php artisan migrate`:
- `add_status_and_term_to_timetables_table` — adds `status` (draft/final, default draft) and an
  optional `term` label. **Important**: this migration also backfills every timetable row that
  already existed to `status = 'final'` in the same step, so nothing you'd already built
  disappears from teacher views the moment this deploys.

**The timetable builder is completely rebuilt.** The old flat "add several rows, submit as a
batch" form is gone. In its place:

1. Go to **Timetable** as principal. You'll see day tabs across the top (Monday–Sunday) and,
   within each day, every class listed with its current sessions shown as small removable chips.
2. Each class has its own compact "add session" row right there — pick the subject/teacher, type
   a start and end time, optionally a room, hit **+ Add session**. No more numbered rows, no more
   losing track of which entry in a big batch had the problem.
3. Try leaving a field blank or entering an invalid time — the field highlights red immediately,
   client-side, before you even submit.
4. Try double-booking a teacher (same teacher, overlapping time, same day) — you'll get a live red
   warning as soon as you pick the time, not just after a round trip to the server.
5. Every new session starts as **draft** (gold badge on its chip) — teachers don't see it yet.
   When a class's schedule is ready, hit **Publish this class's timetable** — every draft session
   for that class (across all days) flips to **final** (green badge) and immediately becomes
   visible on that class's teachers' own timetable pages.
6. The optional label field at the top ("Term 2 2026 - Draft 1", etc.) gets attached to every
   session you add while it's filled in — purely for your own reference when looking at a class's
   session list later, doesn't affect visibility on its own.

**On the "which day failed" complaint:** this is solved structurally rather than by rewording
messages — since each session is now added one at a time through its own small form, any error
lands directly on that specific form (with the exact field highlighted and a specific message),
not buried in a numbered list of a large batch.

**Bulk-import error messages elsewhere** (students, teachers) already named the specific
person/record involved rather than just "this row failed" — I tightened the two remaining cases
where the row's *own* name/admission number was itself the missing piece, so they now show
whatever other identifying detail is available on that row instead of nothing.

## Trying out Phase 13 (Terms, and a real fee ledger)

**Five new migrations** — run `php artisan migrate`. Two of them move real data around, so
read before running on a production database:
- `create_terms_table` — the new Term entity.
- `add_term_id_to_exams_table` — backfills a real Term row for every distinct term/year
  string your exams already used, and links each exam to it.
- `add_term_id_to_timetables_table` — adds the column; Phase 12's free-text term labels
  are *not* auto-converted (too unreliable to parse), so old sessions stay unlinked.
- `create_fee_transactions_table` — the new ledger table.
- `migrate_fee_records_to_fee_transactions` — **this one matters**: it converts every
  existing `fee_records` row into ledger transactions (a debit for the amount due, a
  credit for whatever was already marked paid), computes correct running balances, then
  **drops the `fee_records` table**. Your fee history is preserved, not lost, but this
  is a one-way migration — back up your database first if it holds real data.

### The fee ledger (this was the actual bug)

The root problem: `fee_records` was one mutable row per term. Posting Term 3's fees
didn't *overwrite* Term 2's row in the database, but there was no running balance
connecting them — a parent looking at Term 3 had no way to see they still owed money
from Term 2 unless they manually added up old rows themselves. That's fixed now:

1. Go to **Fees** as principal — this is now a list of students with fee history and
   their **current balance** (not a flat table of records).
2. **Record a charge** (a debit) for a student, or **bulk post** one to a class/whole
   school — same bulk pattern as before.
3. Click **View ledger** on a student — this is the real fix: every transaction in
   order, with the running balance shown after each one, like a bank statement.
4. **Record a payment** (a credit) against that student. Watch the balance drop by
   exactly that amount — it's carried forward from whatever they already owed, not
   reset to a fresh per-term number.
5. Try deleting a transaction from the middle of a student's history — every balance
   after it recalculates automatically, so nothing drifts out of sync.
6. As a **parent**, the Fees page is now a statement too: date, description, term,
   charged/paid columns, and running balance — same "no payment happens here" banner
   as before.

### Terms as a real entity

1. Go to **Terms** (new nav item) and add your school's terms — e.g. "Term 1", "Term 2",
   "Term 3" for the current year. Mark one as **current**.
2. **Exams**: the creation form now has a Term dropdown (defaulting to whichever term is
   current) instead of free-text term/year boxes.
3. **Timetable**: the "label for new sessions" bar at the top of the builder is now a
   real Term dropdown too, not free text.
4. **Fees**: charges and payments can optionally be tagged with a term.

This is what makes "show me Term 2 2026's marks/fees/timetable" an actual reliable
query going forward, rather than hoping a free-text label was typed the same way twice.

**Naming note for anyone reading the code:** `Exam` and `Timetable` already had raw
`term` string columns from before this phase. A relationship method named `term()`
would have silently never fired, since Eloquent always prefers a real database column
over a same-named relation. Both models expose the new relation as `academicTerm()`
instead — worth knowing if you're extending this yourself.

## What's next

- ~~Phase 2 — Super Admin: create/manage schools, create principal accounts~~ ✅ Done
- ~~Phase 3 — Principal: manage classes, subjects, teacher assignments, approve exams~~ ✅ Done
- ~~Phase 4 — Teacher: student management, Excel marks upload, timetable view~~ ✅ Done
- ~~Phase 5 — Parent: guided first-login, dashboard, results, fees, notices~~ ✅ Done
- ~~Phase 6 — Public index page~~ ✅ Done
- ~~Phase 7 — Bulk operations: student import, teacher creation, timetable entry~~ ✅ Done
- ~~Phase 8 — Fees & Notices staff-side UI~~ ✅ Done
- ~~Phase 9 — Year-end promotion workflow (review, then promote)~~ ✅ Done
- ~~Phase 10 — Class-teacher-only exam submission + missing-marks tracker + multi-class exam creation~~ ✅ Done
- ~~Phase 11 — Subject enrollment (electives), teacher-subject qualifications, results totals/average/mean grade~~ ✅ Done
- ~~Phase 12 — Grid-based timetable builder, draft/publish workflow, client-side validation, clearer bulk-error messages~~ ✅ Done
- ~~Phase 13 — Term entity across exams/timetable/fees, fee ledger with debit/credit/running balance~~ ✅ Done
- ~~Phase 14 — UI bug fixes (pagination, table/sidebar overlap, button sizing), sticky
  sidebar, responsive mobile nav, Change Password (all roles), Forgot Password~~ ✅ Done
- ~~Phase 15 — Results ranked by performance, fees ordered by balance, students ordered
  by relevance to the viewing teacher, elective scheduling-conflict prevention,
  teacher-editable marks, class detail view, dashboard risk-scanner insights~~ ✅ Done
- ~~Phase 16 — Full visual redesign: Makueni County blue/green brand palette, Poppins
  font, rounder corners, hover interactions~~ ✅ Done

## Deployment

See **`DEPLOY.md`** for the full GitHub → Render (Docker) → Aiven MySQL walkthrough,
including:
- The exact environment variables Render needs
- Aiven's SSL certificate requirement and how it's handled
- **Setting up real email** (required for Forgot Password to actually deliver —
  `MAIL_MAILER=log` by default just writes to Render's logs, nobody receives anything)
- **Troubleshooting bulk-import/spreadsheet errors** — almost always either a
  `composer.lock` out of sync with `composer.json` (re-run `composer require
  phpoffice/phpspreadsheet` and push), or PHP's default upload/memory limits being
  too small (fixed via `docker/php-overrides.ini`, bundled into the Docker image)
