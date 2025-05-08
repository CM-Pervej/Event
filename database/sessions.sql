
CREATE TABLE event_session (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  session_id INT NOT NULL,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);


CREATE TABLE sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  session_date DATE,
  session_time TIME,
  speaker_id INT,
  event_id INT NOT NULL,  -- Foreign key to events table
  image_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (speaker_id) REFERENCES speakers(id) ON DELETE SET NULL,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE  -- Foreign key to events table
);
