CREATE TABLE tbl_users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,  -- Unique ID for the user
    username VARCHAR(60) DEFAULT NULL, -- user's telegram username
    first_name VARCHAR(60) DEFAULT NULL, -- user's first name
    last_name VARCHAR(60) DEFAULT NULL, -- user's last name
    tg_name VARCHAR(100) DEFAULT NULL, -- Telegram first name and last name
    tg_id BIGINT NOT NULL, -- Telegram user ID
    step VARCHAR(50) DEFAULT NULL, -- Step of the user in the bot
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Timestamp for when the user was created
);

CREATE TABLE tbl_notification_lists (
    id BIGINT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for the list
    list_name VARCHAR(100) NOT NULL, -- Name of the list
    list_owner_id BIGINT NOT NULL, -- Foreign key to tbl_users
    list_lastest_update TIMESTAMP DEFAULT NULL, -- Timestamp for when the list was last updated
    list_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp for when the list was created
    is_deleted BOOLEAN DEFAULT FALSE, -- Flag to mark if the list is deleted
    task_adding_rule TINYINT NOT NULL DEFAULT 1, -- 0: owner, 1: admin, 2: subs
    FOREIGN KEY (list_owner_id) REFERENCES tbl_users(id) ON DELETE CASCADE -- If the owner is deleted, the list is deleted
);

CREATE TABLE tbl_list_subscribers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    list_id BIGINT NOT NULL, -- Foreign key to tbl_notification_lists
    user_id BIGINT NOT NULL, -- Foreign key to tbl_users
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp for when the user subscribed
    FOREIGN KEY (list_id) REFERENCES tbl_notification_lists(id) ON DELETE CASCADE, -- If the list is deleted, the subscriber is deleted
    FOREIGN KEY (user_id) REFERENCES tbl_users(id) ON DELETE CASCADE -- If the user is deleted, the subscription is deleted
);


CREATE TABLE tbl_tasks (
    id BIGINT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for the task
    list_id BIGINT NOT NULL,    -- Foreign key to tbl_notification_lists
    task_name VARCHAR(100) NOT NULL, -- Name of the task
    task_description TEXT DEFAULT NULL, -- Description of the task
    task_date TIMESTAMP DEFAULT NULL, -- Date and time of the task
    is_end BOOLEAN DEFAULT FALSE, -- Flag to mark if the task is completed
    is_deleted BOOLEAN DEFAULT FALSE, -- Flag to mark if the task is deleted
    FOREIGN KEY (list_id) REFERENCES tbl_notification_lists(id) ON DELETE CASCADE -- If the list is deleted, the task is deleted
);

CREATE TABLE tbl_list_admins (
    id BIGINT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for the admin
    user_id BIGINT NOT NULL,    -- Foreign key to tbl_users
    list_id BIGINT NOT NULL,   -- Foreign key to tbl_notification_lists
    FOREIGN KEY (user_id) REFERENCES tbl_users(id) ON DELETE CASCADE, -- If the user is deleted, the admin is deleted
    FOREIGN KEY (list_id) REFERENCES tbl_notification_lists(id) ON DELETE CASCADE -- If the list is deleted, the admin is deleted
);
