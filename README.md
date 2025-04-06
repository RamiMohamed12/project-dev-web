# Project Dev Web

Welcome to the Project Dev Web repository! This project is primarily developed using PHP, CSS, HTML, and JavaScript.

## Table of Contents
- [Introduction](#introduction)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)

## Introduction
Project Dev Web is a web application designed to [provide a brief description of what the project does]. This project aims to [state the main goal or purpose of the project].

## Features
- Feature 1: [description of feature 1]
- Feature 2: [description of feature 2]
- Feature 3: [description of feature 3]

## Technologies Used
- **PHP**: 73.7%
- **CSS**: 17.8%
- **HTML**: 5%
- **JavaScript**: 3.5%

## Installation
To get a local copy up and running, follow these steps:

1. **Clone the repository**:
    ```bash
    git clone https://github.com/RamiMohamed12/project-dev-web.git
    ```

2. **Navigate to the project directory**:
    ```bash
    cd project-dev-web
    ```

3. **Install dependencies**:
    ```bash
    composer install
    ```

4. **Set up the database**:
    - Update `config.php` with your MariaDB credentials:
        ```php
        $dbname = "database.sql";
        $username = "your_mariadb_username";
        $password = "your_mariadb_password";
        ```
    - Import the `database.sql` file into your MariaDB:
        ```bash
        mysql -u your_mariadb_username -p your_mariadb_password < path/to/database.sql
        ```

5. **Run the application**:
    ```bash
    php -S localhost:8000
    ```

## Usage
[Provide instructions and examples for using the application. Include screenshots if necessary.]

## Contributing
Contributions are what make the open-source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License
Distributed under the MIT License. See `LICENSE` for more information.

---

**Maintainer**: [Rami Mohamed](https://github.com/RamiMohamed12)
