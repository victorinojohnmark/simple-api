-- Table for storing user information (for authentication)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,

    created_at INT NOT NULL,
	created_by INT NOT NULL,
    updated_at INT NULL,
	updated_by INT NULL,

	FOREIGN KEY (created_by) REFERENCES users(id),
	FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Table for storing CSRF tokens
CREATE TABLE csrf_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    user_id INT NULL,
    created_at INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for storing pizzas (example module)
CREATE TABLE pizzas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT NULL,

    created_at INT NOT NULL,
	created_by INT NOT NULL,
    updated_at INT NULL,
	updated_by INT NULL,
	deleted_at INT NULL,
	
	FOREIGN KEY (created_by) REFERENCES users(id)
);