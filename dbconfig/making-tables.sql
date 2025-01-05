-- Table for storing user information
CREATE TABLE Users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) DEFAULT NULL,
    first_name VARCHAR(60) DEFAULT NULL,
    last_name VARCHAR(60) DEFAULT NULL,
    tg_name VARCHAR(100) DEFAULT NULL,
    tg_id BIGINT NOT NULL, --Telegram user ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for storing activities
CREATE TABLE Activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);

-- Table for storing broadcast lists
CREATE TABLE BroadcastLists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES Users(id) ON DELETE CASCADE
);

-- Table for storing members of broadcast lists
CREATE TABLE BroadcastListMembers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    list_id INT NOT NULL,
    user_id BIGINT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (list_id) REFERENCES BroadcastLists(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);
