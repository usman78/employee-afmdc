AFMDC Employee Portal

A complete HR and Attendance Management System built with Laravel

Project Overview

The AFMDC Employee Portal is a web-based application developed for Aziz Fatimah Medical & Dental College (AFMDC) to automate and manage internal HR operations.
It provides a centralized platform for employees, teachers, students, and administrators to manage attendance, leave applications, service requests, and internal workflows efficiently.

The system is built using Laravel 11, with an Oracle Database backend, and follows modern development practices to ensure scalability, security, and maintainability.

Features
Authentication System

Custom login system integrated with Oracle database

Users authenticate using:

Employee Code

Password (stored in existing Oracle tables)

Role-based access control:

Admin

Teacher

Student

HOD / Department Users

Attendance Management

Face and thumbprint-based attendance tracking

Attendance logs stored in Oracle

Automated attendance reports

Late/Early minutes calculation

Daily and monthly attendance summaries

Department-wise attendance views

Leave Management

Online leave application system

Multiple leave categories:

Medical

Annual

Casual

Short / Half leave

Date-range based leave reports

Approval workflow

Leave balance calculations

Reporting System

Dynamic reports with filters:

Department

Date Range

Employee

PDF report generation using:

Barryvdh/DomPDF

Printable formatted reports

Excel-ready data exports

Service Request Module

Employees can submit internal service requests

Track status of requests

Admin dashboard for request management

Categorized request types

Technologies Used
Layer	Technology
Backend	Laravel 11 (PHP)
Database	Oracle Database
Frontend	Blade Templates + Bootstrap 5
JavaScript	jQuery, Vanilla JS
PDF Generation	Barryvdh DomPDF
Version Control	Git & GitHub
Environment	XAMPP / Apache
Authentication	Custom Oracle-based Auth Provider
Project Structure Highlights

Custom Authentication Provider

Oracle Database Integration

Modular Controllers

Reusable Blade Components

Service Layer Architecture

Clean MVC Pattern
