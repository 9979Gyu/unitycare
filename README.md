<p align="center"><img src="https://github.com/user-attachments/assets/c6f6d996-a5d3-45ee-9f81-b7e682f47cfc" width="200"></p>

## UnityCare - An Application to Empower Community Employment and Skill

<p>UnityCare is an application designed to address the various challenges posed by poverty and disability. It serves as a comprehensive platform that:</p>

<div class="container"><ol><li><b>Facilitating Accessible Employment Opportunities:</b><p>Enables enterprises to post vacancies designed specifically for individuals with disabilities, ensuring a tailored fit between job requirements and applicant abilities. Increase the visibility and accessibility of job opportunities for vulnerable groups, thereby increasing their chances of getting a job and improving their quality of life.</p></li>
<li><b>Encourage Skill Development and Volunteerism:</b><p>Engage volunteers to organize and promote skills development programs adapted for individuals with disabilities, foster personal growth and improve employability. Providing a platform for volunteers to contribute to society by supporting educational and vocational initiatives, further building a more inclusive and empathetic society. </p></li>
<li><b>Raise Awareness and Build Empathy:</b><p>Share comprehensive poverty-related information to raise public awareness of the challenges faced by individuals living in poverty and those with disabilities. Cultivate a culture of empathy and collective responsibility, encouraging community members to take proactive steps towards poverty reduction and social equality.</p></li></ol></div>

<p>Through UnityCare, we aim to create a supportive environment where businesses, volunteers and individuals with disabilities can effectively work together to promote economic resilience and social inclusion. These initiatives not only aim to alleviate immediate financial hardship but also seek to build a sustainable path towards long-term economic stability and community well-being.</p>

## Requirements

OCR Engine Tesseract should be install in the system. Follow Tesseract installation guide <a href="https://github.com/tesseract-ocr/tessdoc#compiling-and-installation">here</a>. Make sure from the command line you have the tesseract command available（e.g. Tesseract --help).

## To set up, follow these steps:

<p>Clone the GitHub repository: git clone https://github.com/9979Gyu/unitycare.git</p>
<p>Navigate to the project directory: cd your-repo</p>
<p>Create a MySQL database.</p>
<p>Configure the database in the .env file with your MySQL credentials.</p>
<p>Install dependencies: composer install</p>
<p>Set up the database: php artisan migrate</p>
<p>Insert the require data</p>

## System Modules (General)

<ol>
    <li>
        <b>Authentication Module</b>
        <ul>
            <li>Login - To log in to the system with account</li>
            <li>Logout - To end session</li>
            <li>Change Password - To reset password of an account</li>
        </ul>
    </li><br>
    <li>
        <b>User Management Module</b>
        <ul>
            <li>Create User - To add user or create account by role</li>
            <li>View User - To list users or view personal profile</li>
            <li>Update User - To update account details</li>
            <li>Delete User - To inactivate account</li>
            <li>Verify User - To verify email and activate account</li>
        </ul>
    </li><br>
    <li>
        <b>Program Management Module</b>
        <ul>
            <li>Create Program - To add volunteering or skill development program</li>
            <li>View Program - To list programs based on conditions</li>
            <li>Update Program - To update selected program details</li>
            <li>Delete Program - To inactivate program</li>
            <li>Generate Program Chart - To display program classification chart</li>
            <li>Export Program Report - To download list of programs based on condition in Excel file</li>
            <li>Create Program Participation - To participate a program</li>
            <li>View Program Participation - To display list of participated program or list of participants of selected program</li>
            <li>Update Program Participation - To update the approval status of the program or user participation</li>
            <li>Delete Program Participation - To inactivate user participation</li>
            <li>Generate Program Participation Chart - To display participation classification chart</li>
            <li>Export Program Participation Report - To download list of participated program or list of participants of selected program based on condition in Excel file</li>
            <li>Print Certificate - To download participation certificate in PDF file</li>
        </ul>
    </li><br>
    <li>
        <b>Job Vacancy Management Module</b>
        <ul>
            <li>Create Job Vacancy - To add job offer, type of job (e.g. Engineering) or type of job shift (e.g. Full time)</li>
            <li>View Job Vacancy - To list job offers by enterprise, type of jobs or type of job shift</li>
            <li>Update Job Vacancy - To update job details</li>
            <li>Delete Job Vacancy - To inactivate job offer</li>
            <li>Generate Job Vacancy Chart - To display job offer classification chart by type of job</li>
            <li>Export Job Vacancy Report - To download list of job offers based on condition in Excel file</li>
            <li>Create Job Application - To apply job</li>
            <li>View Job Application - To display list of applied jobs or list of applicants</li>
            <li>Delete Job Application - To inactivate job application</li>
            <li>Update Job Application - To update the approval status of the job offer or user's application</li>
            <li>Generate Job Application Chart - To display job offer classification chart by type of job</li>
            <li>Export Job Application Report - To download list of applied job offers based on condition in Excel file</li>
        </ul>
    </li><br>
    <li>
        <b>Transaction Management Module</b>
        <ul>
            <li>Create Transaction - To donate or pay for program registration fee</li>
            <li>View Transaction - To display list of transactions based on condition</li>
            <li>Delete Transaction - To remove transaction record</li>
            <li>Export Transaction Report - To download list of transaction records based on condition in Excel file</li>
            <li>Print Transaction - To download receipt of selected transaction</li>
        </ul>
    </li>
</ol>

## Quick Walkthrough

<div align="center">
    <a href="https://youtu.be/ab_lJo7JxxQ" target="_blank">
        <img src="https://img.youtube.com/vi/ab_lJo7JxxQ/hqdefault.jpg" width="300" border_radius="5" alt="Quick Walkthrough Video">
    </a>
</div>

## Example

<div align="center">
    <p border><img src="https://github.com/user-attachments/assets/56d1cb71-9d35-4cb7-940a-22cd682653ac" alt="UnityCare Dashboard"></p>
    <p><img src="https://github.com/user-attachments/assets/835921a0-625e-43db-bd2a-d3ad2444235d" alt="UnityCare Job Offer"></p>
    <p><img src="https://github.com/user-attachments/assets/729f0ecb-9050-46d5-9721-182b01ebfb12" alt="UnityCare Job Offer"></p>
    <p><img src="https://github.com/user-attachments/assets/bddbfd0b-d832-40de-a1d3-036bcd62d7cd" alt="UnityCare Programs"></p>
</div>

<!-- <p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). -->
