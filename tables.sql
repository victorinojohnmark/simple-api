-- Table for storing user information (for authentication)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,

    created_at INT(11) NOT NULL,
	created_by INT(11) NULL,
    updated_at INT(11) NULL,
	updated_by INT(11) NULL,

	FOREIGN KEY (created_by) REFERENCES users(id),
	FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Table for storing CSRF tokens
CREATE TABLE csrf_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    user_id INT(11) NULL,
    created_at INT(11) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table for storing pizzas (example module)
CREATE TABLE pizzas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT NULL,

    created_at INT(11) NOT NULL,
	created_by INT(11) NOT NULL,
    updated_at INT(11) NULL,
	updated_by INT(11) NULL,
	deleted_at INT(11) NULL,
	
	FOREIGN KEY (created_by) REFERENCES users(id)
);