# LMS-UserAccount2

CodeIgniter 3 based Learning Management System (LMS) web application with student, instructor, and admin capabilities.

## 1) High-Level Architecture

```mermaid
flowchart LR
    U[Browser / Client] --> A[Apache + mod_rewrite]
    A --> I[index.php Front Controller]
    I --> R[CodeIgniter Router]
    R --> C[Controller Layer]
    C --> M[Model Layer]
    M --> DB[(MySQL/MariaDB)]
    C --> V[View Layer]
    V --> U
    C --> S[(Session Storage: ci_sessions table)]
    C --> FS[(File Storage: uploads/*)]
```

## 2) Module Design

```mermaid
flowchart TB
    subgraph Public Site
      H[Home Controller]
      L[Login Controller]
      PR[Profile Controller]
    end

    subgraph Instructor Area
      T[UserAccount/Teacherr]
      CQ[Course + Quiz Ops]
    end

    subgraph Admin Area
      AP[UserAccount/Profile Admin Ops]
      CS[Course/*]
      EN[Administratori/Enrollment]
      ST[Administratori/Settings]
    end

    subgraph Shared Services
      CR[Crud_model]
      UM[User_model]
      PM[Payment_model]
      EM[Email_model]
      VM[Video_model]
      FM[Feedback_model]
      HL[Helpers: common/user/multi_language]
    end

    H --> CR
    L --> UM
    L --> EM
    T --> CR
    T --> VM
    CS --> CR
    EN --> CR
    ST --> CR
    AP --> UM
    PM --> UM
```

## 3) Request and Role Flow

### Role-based flow
```mermaid
flowchart TD
    G[Guest] -->|Sign up / Login| U[User role_id=2]
    U -->|Create/Edit course| P[Course status: pending or draft]
    A[Admin role_id=1] -->|Approve| AC[Course status: active]
    U -->|Buy course| E[Enrolments + Payments]
    AC -->|Visible on catalog| C[Public Courses]
```

### Checkout flow
```mermaid
sequenceDiagram
    participant Student
    participant HomeController
    participant PaymentModel
    participant CrudModel
    participant DB

    Student->>HomeController: POST /home/stripe_checkout or paypal_checkout
    Student->>HomeController: POST /home/payment_success/{method}/{userId}/{amount}
    HomeController->>PaymentModel: stripe_payment(...) (stripe only)
    PaymentModel->>DB: validate user + execute charge metadata
    HomeController->>CrudModel: enrol_student(user_id)
    HomeController->>CrudModel: course_purchase(user_id, method, amount)
    CrudModel->>DB: INSERT enrol + payment rows
    HomeController-->>Student: redirect /home + flash success
```

## 4) Project Structure

```text
/
|- application/
|  |- config/                  # CI config (routes, db, autoload, sessions)
|  |- controllers/             # Feature-oriented controllers
|  |  |- Home.php              # Public pages, learning, cart, checkout
|  |  |- UserAccount/          # Login, profile, instructor
|  |  |- Course/               # Admin course/category/lesson/quiz ops
|  |  |- Administratori/       # Admin settings + revenue + enrollment
|  |  |- Payment/              # Cart + payment operations
|  |  |- Messages/             # Private messaging
|  |  `- Feedbacks/            # Ratings
|  |- models/                  # Domain and data logic
|  |- views/                   # Frontend + backend templates/fragments
|  |- helpers/                 # common/user/multi-language helpers
|  `- libraries/Stripe/        # Stripe SDK (embedded)
|- assets/                     # Static frontend/backend assets
|- db/dbl.sql                  # SQL schema + seed data
|- ci_sessions/                # Session path directory
|- uploads/                    # Thumbnails, lesson files, system media
|- index.php                   # CI front controller
`- .htaccess                   # Rewrite to index.php
```

## 5) API Surface (HTTP Endpoints)

This app is mostly server-rendered HTML + form/AJAX endpoints (not a pure REST API).

### Public and Learning
- `GET /` -> default route to `home`
- `GET /home/courses` (supports query filters: category, price, level, language, rating)
- `GET /home/course/{slug}/{courseId}`
- `GET /home/lesson/{slug}/{courseId}/{lessonId?}`
- `GET /home/instructor_page/{instructorId}`
- `GET /home/search?query=...`
- `GET /home/about_us`, `/home/terms_and_condition`, `/home/privacy_policy`

### Authentication and User Account
- `POST /useraccount/login/validate_login/{from?}`
- `POST /useraccount/login/register`
- `POST /useraccount/login/forgot_password/{from?}`
- `GET /useraccount/login/logout/{from?}`
- `GET /useraccount/login/verify_email_address/{verificationCode}`
- `GET|POST /home/profile/{user_profile|user_credentials|user_photo}`
- `POST /home/update_profile/{update_basics|update_credentials|update_photo}`

### Cart and Checkout
- `POST /home/handleCartItems`
- `POST /home/handleCartItemForBuyNowButton`
- `POST /home/paypal_checkout`
- `POST /home/stripe_checkout`
- `POST /home/payment_success/{method}/{userId}/{amountPaid}`

### Instructor Endpoints
- `GET /useraccount/teacherr/courses`
- `POST /useraccount/teacherr/course_actions/{add|edit|delete|draft|publish}/{courseId?}`
- `POST /useraccount/teacherr/sections/{courseId}/{add|edit|delete}/{sectionId?}`
- `POST /useraccount/teacherr/lessons/{courseId}/{add|edit|delete}/{lessonId?}`
- `POST /useraccount/teacherr/quizes/{courseId}/{add|edit|delete}/{quizId?}`
- `POST /useraccount/teacherr/quiz_questions/{quizId}/{add|edit|delete}/{questionId?}`
- `GET|POST /useraccount/teacherr/payment_settings`
- `GET|POST /useraccount/teacherr/instructor_revenue`

### Admin Endpoints
- `GET /useraccount/profile/dashboard`
- `GET|POST /useraccount/profile/users/{add|edit|delete}/{userId?}`
- `GET /course/course/courses`
- `GET /course/course/pending_courses`
- `POST /course/course/course_actions/{add|edit|delete}/{courseId?}`
- `POST /course/course/change_course_status/{updated_status}`
- `GET|POST /course/kategorite/categories/{add|edit|delete}/{categoryId?}`
- `GET|POST /administratori/enrollment/enrol_history`
- `POST /administratori/enrollment/enrol_student/enrol`
- `GET|POST /administratori/settings/system_settings`
- `GET|POST /administratori/settings/frontend_settings`
- `GET|POST /administratori/settings/payment_settings`
- `GET|POST /administratori/settings/smtp_settings`
- `GET|POST /administratori/settings/manage_language`

### Messaging and Feedback
- `GET|POST /messages/message/message/{message_home|message_read|send_new|send_reply}`
- `GET|POST /home/my_messages/{read_message|send_new|send_reply}/{threadCode?}`
- `POST /home/rate_course`
- `POST /feedbacks/rate/rate_course`

### AJAX/Fragment endpoints
- `POST /home/handleWishList` -> HTML fragment (`wishlist_items`)
- `POST /home/handleCartItems` -> HTML fragment (`cart_items`)
- `POST /home/refreshShoppingCart` -> HTML fragment
- `POST /home/my_courses_by_category` -> HTML fragment
- `POST /home/reload_my_wishlists` -> HTML fragment
- `POST /home/get_course_details` -> plain text title
- `POST /home/isLoggedIn` -> plain text `true|false`
- `POST /course/ligjerata/ajax_get_video_details` -> duration text
- `POST /course/course/ajax_get_section` -> HTML fragment
- `POST /course/kategorite/ajax_get_sub_category` -> HTML fragment

## 6) Data Model (Core Entities)

```mermaid
erDiagram
    USERS ||--o{ COURSE : creates
    USERS ||--o{ ENROL : enrolls
    COURSE ||--o{ ENROL : has
    COURSE ||--o{ SECTION : has
    SECTION ||--o{ LESSON : contains
    LESSON ||--o{ QUESTION : quiz_questions
    USERS ||--o{ PAYMENT : pays
    COURSE ||--o{ PAYMENT : generates
    USERS ||--o{ RATING : writes
    COURSE ||--o{ RATING : receives
    USERS ||--o{ MESSAGE_THREAD : sender_or_receiver
    MESSAGE_THREAD ||--o{ MESSAGE : contains
    CATEGORY ||--o{ CATEGORY : parent_child
    CATEGORY ||--o{ COURSE : categorizes
```

Primary tables from dump: `users`, `role`, `course`, `category`, `section`, `lesson`, `question`, `enrol`, `payment`, `rating`, `message_thread`, `message`, `settings`, `frontend_settings`, `currency`, `ci_sessions`.

## 7) Runtime Characteristics

- Routing is mostly conventional CodeIgniter URI mapping (`/controller/method/...`).
- `default_controller` is `home`.
- URL rewriting is done by `.htaccess` to `index.php`.
- `base_url` is dynamically built from request host and script path.
- Session driver is configured to `database` with save path table `ci_sessions`.
- Theme selection is read from `frontend_settings.theme` and used to resolve view folder (`frontend/default/...`).

## 8) Current Review Findings (Architecture/Code Quality)

- **Controller compile blocker:** `application/controllers/Payment/Llojipageses.php` redeclares `invoice()` and `payment_success()`, which causes a fatal parse error when loaded.
- **Legacy PHP compatibility risk:** `system/libraries/Profiler.php` contains deprecated curly-brace string offset syntax (breaks under modern PHP lint in this environment).
- **Database naming mismatch:** app config uses `dbuseraccount` while SQL dump header references `dblearning`.
- **Undefined model methods referenced:** instructor payment settings call `update_instructor_paypal_settings` and `update_instructor_stripe_settings`, but these methods are not present in scanned models.
- **Controller method mismatch:** `Course::preview()` calls `is_the_course_belongs_to_current_instructor()` but that method exists in `Teacherr`, not in `Course`.
- **Large model duplication:** `User_model` and `Crud_model` share significant overlapping behavior, making maintenance and testing harder.
- **Security posture is legacy:** SHA1 password hashing, CSRF disabled in config, many endpoints rely on session checks + redirects instead of centralized policy middleware.

## 9) Suggested High-Level Refactor Path

1. Stabilize runtime blockers (duplicate methods, missing method calls, route alias consistency).
2. Consolidate domain logic into bounded services (CourseService, EnrollmentService, PaymentService, MessagingService).
3. Split AJAX JSON APIs from page controllers for clearer contracts and testability.
4. Introduce stronger auth/security baseline (password hashing upgrade, CSRF enablement, centralized authorization checks).
5. Add automated tests around purchase/enrollment and course status transitions.
