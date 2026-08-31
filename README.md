# Muslim Connect

Muslim Connect is a mobile application designed to help Muslims organize and manage important aspects of their spiritual and daily lives.

The application brings together spiritual practices, financial commitments, charitable activities, and support during moments of bereavement, with a strong community-oriented dimension.

📱 **Available on Google Play:**  
https://play.google.com/store/apps/details?id=com.elh.app

---

## 📱 About Muslim Connect

Muslim Connect centralizes several aspects of everyday Muslim life in a single platform.

The application covers:

- 🕌 Prayer and acts of worship
- 💰 Management of financial commitments, including debts, loans, and amanas
- 🤲 Charitable contributions
- 🕊️ Support during moments of bereavement
- 📜 Shared wills
- 🕌 Salat al-Janaza
- 👥 Community-oriented features

The goal is to help users organize their commitments and responsibilities with greater peace of mind, while supporting them in their daily life and in preparing for the hereafter.

---

## ✨ Main Features

### 🕌 Spiritual Life

Muslim Connect provides functionality designed to support everyday spiritual practice and acts of worship.

### 💰 Financial Commitments

Users can manage and track different types of financial commitments, including:

- Debts
- Loans
- Amanas
- Installments
- Remaining balances
- Payment status

The application includes automatic remaining-balance calculations for installment-based obligations.

### 🤲 Charity

The application provides functionality related to charitable contributions and organization.

### 🕊️ Bereavement & Community

Muslim Connect includes community-oriented functionality for important moments such as:

- Bereavement
- Shared wills
- Salat al-Janaza

---

# 🏗️ Technical Architecture

Muslim Connect uses a mobile-to-backend architecture.

```text
┌──────────────────────────────┐
│       Flutter Mobile App     │
│                              │
│   UI · Navigation · Services │
└──────────────┬───────────────┘
               │
               │ REST API
               │ JWT Authentication
               ▼
┌──────────────────────────────┐
│       Symfony Backend        │
│                              │
│ Controllers · Services       │
│ Authentication · API         │
└──────────────┬───────────────┘
               │
               │ Doctrine ORM
               ▼
┌──────────────────────────────┐
│            MySQL             │
│                              │
│ Users · Obligations ·        │
│ Installments · Messages      │
└──────────────────────────────┘

               │
               ▼
┌──────────────────────────────┐
│           Firebase           │
│                              │
│ Cloud Messaging · Storage    │
└──────────────────────────────┘
🛠️ Tech Stack

Mobile
Flutter
Dart
Backend
Symfony
PHP
REST API
JWT Authentication
Doctrine ORM
Database
MySQL
Firebase
Firebase Cloud Messaging
Firebase Storage
Development & API Tools
Git
GitHub
Docker
Postman

🔐 Authentication

The application uses JWT-based authentication to secure communication between the mobile application and the Symfony backend.

Authenticated requests are sent to protected API endpoints using JWT tokens.

Security: Production credentials, JWT signing keys, Firebase credentials, and environment-specific secrets should never be committed to the repository.

💰 Financial Management

One of the core components of Muslim Connect is financial obligation management.

The application supports:

Financial Obligation
        │
        ├── Amount
        ├── Status
        ├── Remaining Amount
        │
        └── Installments
                │
                ├── Amount
                ├── Payment Status
                └── Payment Date

The backend manages the associated data using Symfony, Doctrine ORM, and MySQL.

Installment payments can be tracked while the remaining balance is automatically calculated.

🔔 Notifications

Muslim Connect integrates Firebase Cloud Messaging (FCM) to support push notifications.

Notifications provide users with updates from the application, including events related to the application's functionality.

☁️ Cloud Storage

The application integrates Firebase Storage for cloud-based file uploads.

This allows files to be stored remotely while the application manages the associated data through its backend.

💬 Communication

The backend includes support for communication-related data, including:

Chat threads
Messages
Participants

This provides the foundation for communication functionality within the application.

📂 Backend Structure

The Symfony backend follows a standard Symfony project structure.

elh-app/
├── assets/
├── bin/
├── config/
├── migrations/
├── public/
├── src/
├── templates/
├── tests/
├── translations/
├── compose.yaml
├── composer.json
├── package.json
└── symfony.lock
⚙️ Getting Started
Requirements
Backend
PHP
Composer
Symfony CLI
MySQL
Node.js / npm

Docker can also be used for the development environment.

Mobile
Flutter SDK
Dart SDK
Android Studio or another Flutter-compatible development environment
Backend Installation

Clone the repository:

git clone https://github.com/Alibalti592/elh-app.git
cd elh-app

Install PHP dependencies:

composer install

Configure your local environment variables and database connection.

Run database migrations:

php bin/console doctrine:migrations:migrate

Start the Symfony development server:

symfony server:start
Mobile Application

The Flutter mobile application requires the Flutter SDK.

Install dependencies:

flutter pub get

Configure the API base URL for your local environment.

Run the application:

flutter run
🔑 Environment Configuration

Create your local environment configuration according to the requirements of the project.

Example:

APP_ENV=dev
DATABASE_URL=your_database_connection
JWT_SECRET_KEY=your_local_private_key
JWT_PUBLIC_KEY=your_local_public_key

Never commit real credentials, private keys, Firebase credentials, or production environment files to GitHub.

🧪 Testing

The Symfony backend contains a tests/ directory for automated testing.

Run the test suite with:

php bin/phpunit

📱 Google Play

Muslim Connect is available on Google Play.

👉 https://play.google.com/store/apps/details?id=com.elh.app

🎯 Project Highlights

This project provided practical experience across the complete mobile-to-backend development lifecycle.

Mobile Development
Flutter
Dart
Mobile UI development
API integration
Backend Development
Symfony
PHP
REST APIs
JWT authentication
Doctrine ORM
Data Management
MySQL
Financial obligation modeling
Installment tracking
Remaining-balance calculations
Cloud Services
Firebase Cloud Messaging
Firebase Storage
Development Practices
Git & GitHub
Docker
API testing with Postman

📚 Technical Challenges

The project involved working across multiple layers of a full-stack application, including:

Connecting a Flutter mobile client with a Symfony REST API
Implementing JWT-based authentication
Modeling financial obligations and installments
Calculating remaining balances
Integrating Firebase services
Managing communication between mobile and backend components
Structuring backend entities and database relationships

🚀 Future Improvements

Potential improvements include:

Expanding automated test coverage
Improving API documentation
Adding more comprehensive application monitoring
Further improving security and secret management
Expanding mobile UI/UX
Improving deployment and CI/CD automation



Software Engineering Graduate

GitHub:
https://github.com/Alibalti592
