# Paper & Pixels – PHP Blog Application

Paper & Pixels is a modern, responsive blogging platform built with PHP 8 and MySQL. It powers a fully fledged publishing workflow with user authentication, role-based permissions, Markdown editing, likes, comments, and search-friendly tagging — Built from scratch as part of the IN2120 Web Application Development assignment using pure PHP and Tailwind CSS.

## Live Demo

- **Website:** https://paperandpixels.lovestoblog.com

## Overview

Paper & Pixels is a full-stack blog platform where users can:

- Secure registration, login, logout, and session handling
- Rich post authoring with Markdown, cover images, and live previews
- Like/unlike interactions and threaded comment discussions
- Search and tag filters for effortless content discovery
- Responsive, accessibility-aware UI that adapts to any device
- Admin-specific controls for moderating posts across the platform

## Tech Stack

| Layer           | Technology                                                  |
| --------------- | ----------------------------------------------------------- |
| Frontend        | HTML5, Tailwind CSS, JavaScript                             |
| Backend         | PHP 8+ (procedural with sessions)                           |
| Database        | MySQL (MariaDB via InfinityFree)                            |
| Hosting         | InfinityFree free hosting service                           |
| Domain          | .lovestoblog.com subdomain                                  |
| Version Control | Git + GitHub                                                |
| Security        | HTTPS (Auto-SSL), `.env` configuration, prepared statements |

## Core Features

### Authentication and Authorization

- Register/login/logout using `password_hash()` and `password_verify()`
- Secure sessions and cookie handling
- Role-based access (Admin, User, Guest)

### Blog CRUD Operations

- Create, read, update, delete posts
- Upload and display cover images stored in `/uploads`
- Markdown formatting for rich text posts

### Likes and Comments

- Like or unlike posts via a dedicated `likes` table
- Display dynamic like counts
- Comment system with user attribution and timestamps

### Search and Tags

- Search by post title or tags
- Optional tag filters for exploring related content

### User Roles

| Role  | Permissions                    |
| ----- | ------------------------------ |
| Guest | View only                      |
| User  | Create, edit, delete own posts |
| Admin | Manage all posts and users     |

### Responsive UI

- Tailwind CSS utility-first design
- Balanced contrast and typography for readability
- Mobile-friendly navigation with dynamic login/register state

## Project Structure

```
php_blog/
├── index.php
├── config.php
├── phpblogdb.sql
├── README.md
├── admin/
├── api/
│   └── toggle_like.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── images/
│   │   └── favicon.svg
│   └── js/
│       └── app.js
├── auth/
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── includes/
│   ├── auth_check.php
│   ├── footer.php
│   ├── header.php
│   └── navbar.php
├── posts/
│   ├── create.php
│   ├── delete.php
│   ├── edit.php
│   └── view.php
└── uploads/
```

## Security and Configuration

Database credentials stored in `.env`:

```
DB_HOST=sql307.epizy.com
DB_PORT=3306
DB_DATABASE=epiz_12345678_phpblog
DB_USERNAME=epiz_12345678
DB_PASSWORD=your_db_password
DB_CHARSET=utf8mb4
```

Additional safeguards:

- `.htaccess` rules prevent `.env` exposure and enforce HTTPS
- Prepared statements for all database operations
- Error details logged server-side only
- SSL certificate automatically provisioned by InfinityFree

## Database Schema

Tables:

- `users` (`id`, `username`, `password`, `role`)
- `posts` (`id`, `user_id`, `title`, `content`, `tags`, `cover_image`, `created_at`)
- `likes` (`id`, `post_id`, `user_id`, `created_at`)
- `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`)

Relationships:

- One-to-many: `users` → `posts`
- One-to-many: `posts` → `comments`
- Many-to-many: `users` ↔ `posts` through `likes`

## Testing Checklist

| Area     | Test                                                       |
| -------- | ---------------------------------------------------------- |
| Auth     | Register/login/logout with correct and invalid credentials |
| Roles    | Admin manages any post; users manage only their own        |
| CRUD     | Create, edit, delete posts with cover image uploads        |
| Likes    | Like/unlike updates and persists without refresh           |
| Comments | Add and display comments under posts                       |
| Search   | Search by title and tag values                             |
| UI       | Responsive layout on mobile and desktop devices            |
| HTTPS    | Automatic redirect from HTTP to HTTPS                      |
| Database | Tables populated without orphaned records                  |

## Deployment Details

- Hosting: InfinityFree (free PHP hosting)
- Domain: `paperandpixels.lovestoblog.com`
- Database: Imported via phpMyAdmin
- `.env`: Uploaded with production credentials
- SSL: Automatically enabled through InfinityFree
- HTTPS: Enforced via `.htaccess` redirect

## Developer

- Adithya Dilum
- University of Moratuwa
- BSc (Hons) in Information Technology & Management
- Level 02, Semester 01
- IN2120 – WEB PROGRAMMING

## License and Usage

Project created for educational purposes as part of coursework. You may explore, learn from, and extend the code. Please credit Adithya Dilum when reusing the project or its design.

## Acknowledgements

- Tailwind CSS for rapid interface development
- InfinityFree for free PHP hosting
- Official PHP and MySQL documentation
- GitHub Student Developer Pack

## Final Deployment

- **Production:** https://paperandpixels.lovestoblog.com
  Secure, responsive, and feature-complete — ready for storytelling.
