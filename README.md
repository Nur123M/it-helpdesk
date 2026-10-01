\# 🛡️ IT Help Desk



A web-based IT support ticket management system built with \*\*PHP\*\* and \*\*MySQL\*\*. Users can submit technical issues, track their progress, and communicate with IT support staff. Administrators get a dedicated panel to manage, respond to, and resolve tickets.



\---



\## 📖 Table of Contents



\- \[Features](#-features)

\- \[Tech Stack](#-tech-stack)

\- \[Screenshots](#-screenshots)

\- \[Project Structure](#-project-structure)

\- \[Installation](#-installation)

\- \[Usage](#-usage)

\- \[Database Schema](#-database-schema)

\- \[Security Notes](#-security-notes)

\- \[Roadmap](#-roadmap)

\- \[License](#-license)



\---



\## ✨ Features



\### For Users

\- 🔐 \*\*Register and login\*\* with secure password hashing

\- 🎫 \*\*Submit support tickets\*\* with category and priority

\- 📊 \*\*Personal dashboard\*\* showing ticket statistics at a glance

\- 📋 \*\*Track ticket status\*\* — Pending, In Progress, or Resolved

\- 💬 \*\*Conversation thread\*\* with the IT support team on each ticket



\### For Administrators

\- 📈 \*\*Admin dashboard\*\* with system-wide statistics

\- 🗂️ \*\*Manage all tickets\*\* with filtering by status

\- 🔄 \*\*Update ticket status\*\* to reflect progress

\- 💬 \*\*Respond to users\*\* directly on ticket threads

\- 👥 \*\*View user details\*\* and ticket history



\### General

\- 📱 \*\*Fully responsive\*\* design — works on desktop, tablet, and mobile

\- 🎨 \*\*Modern UI\*\* with a clean, professional aesthetic

\- 🔒 \*\*Prepared SQL statements\*\* throughout to prevent injection

\- ✅ \*\*Server-side validation\*\* on all forms



\---



\## 🧰 Tech Stack



| Layer | Technology |

|-------|-----------|

| Backend | PHP (PDO) |

| Database | MySQL / MariaDB |

| Frontend | HTML5, CSS3 (custom) |

| Fonts | Google Fonts (Inter) |

| Server | Apache (XAMPP / Laragon / WAMP) |

| Auth | PHP sessions + `password\_hash()` |



\---



\## 📁 Project Structure

/it-helpdesk

│

├── index.php

├── login.php

├── register.php

├── dashboard.php

├── create\_ticket.php

├── my\_tickets.php

├── ticket.php

│

├── admin/

│   ├── dashboard.php

│   ├── tickets.php

│   └── ticket.php

│

├── config/

│   └── database.php

│

└── assets/

&#x20;   ├── css/

&#x20;   └── js/



\---



\## ⚙️ Installation



\### Prerequisites



Make sure you have:

\- \*\*PHP 7.4+\*\* (8.x recommended)

\- \*\*MySQL 5.7+\*\* or \*\*MariaDB 10.3+\*\*

\- \*\*Apache\*\* (XAMPP, Laragon, WAMP, or MAMP)



\### 1. Clone the Repository



```bash

git clone https://github.com/Nur123M/it-helpdesk.git



Or download the ZIP and extract it into your web root:



Create the Database

Open phpMyAdmin or use the MySQL CLI and run:



sql

CREATE DATABASE it\_helpdesk

&#x20; CHARACTER SET utf8mb4

&#x20; COLLATE utf8mb4\_unicode\_ci;



&#x20;Import the Schema

In phpMyAdmin:



Select the it\_helpdesk database



Click Import



Upload the database.sql file (if provided) and click Go



🔒 Security Notes

This project demonstrates secure PHP practices:



✅ Password hashing with password\_hash() / password\_verify()



✅ Prepared statements (PDO) on every query — protects against SQL injection



✅ Session-based authentication with login checks on protected pages



✅ Role-based access control — admin pages check user\_role



✅ Output escaping with htmlspecialchars() to prevent XSS



✅ Server-side validation on all form submissions



⚠️ Before Deploying to Production

Replace config/database.php credentials with real ones and keep them out of version control



Set display\_errors = Off in php.ini



Enable HTTPS



Add CSRF tokens to all POST forms



Consider rate limiting login attempts



🛣️ Roadmap

Planned and potential future improvements:



□ File attachments on tickets

□ Email notifications on status change

□ Search and filter on user ticket list

□ Ticket assignment to specific admins

□ Admin user management page

□ Export tickets to CSV/PDF

□ Dark mode

□ CSRF protection on forms

□ Automated tests



📄 License

This project is open source and available under the MIT License.



👤 Author

Nur



GitHub: @Nur123M



🙏 Acknowledgements

Built as a learning project for PHP/MySQL web development



UI inspired by modern help desk tools



Fonts by Google Fonts (Inter)

