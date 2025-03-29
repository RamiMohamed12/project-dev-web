-- User Table
CREATE TABLE user (
    id_user BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password TEXT NOT NULL,
    location TEXT,
    phone_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin Table
CREATE TABLE admin (
    id_user BIGINT PRIMARY KEY,
    FOREIGN KEY (id_user) REFERENCES user(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student Table
CREATE TABLE student (
    id_user BIGINT PRIMARY KEY,
    date_of_birth DATE NOT NULL,
    year ENUM('1st', '2nd', '3rd', '4th', '5th') NOT NULL,
    description TEXT,
    FOREIGN KEY (id_user) REFERENCES user(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pilote Table
CREATE TABLE pilote (
    id_user BIGINT PRIMARY KEY,
    FOREIGN KEY (id_user) REFERENCES user(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Company Table
CREATE TABLE company (
    id_company BIGINT PRIMARY KEY AUTO_INCREMENT,
    name_company VARCHAR(255) NOT NULL,
    location TEXT NOT NULL,
    description TEXT,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NOT NULL,
    number_of_students INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Internship Table
CREATE TABLE internship (
    id_internship BIGINT PRIMARY KEY AUTO_INCREMENT,
    id_company BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    remuneration DECIMAL(10,2),
    offre_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_company) REFERENCES company(id_company)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Application Table
CREATE TABLE application (
    id_app BIGINT PRIMARY KEY AUTO_INCREMENT,
    id_user BIGINT NOT NULL,
    id_internship BIGINT NOT NULL,
    cv TEXT NOT NULL,
    cover_letter TEXT NOT NULL,
    application_date DATE DEFAULT (CURDATE()),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES student(id_user),
    FOREIGN KEY (id_internship) REFERENCES internship(id_internship)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wishlist Table
CREATE TABLE wishlist (
    id_wishlist BIGINT PRIMARY KEY AUTO_INCREMENT,
    id_user BIGINT NOT NULL,
    id_internship BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES student(id_user),
    FOREIGN KEY (id_internship) REFERENCES internship(id_internship)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;