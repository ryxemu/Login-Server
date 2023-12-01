// server.js
const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const bodyParser = require('body-parser');
const bcrypt = require('bcrypt');

const app = express();
const PORT = 3000;

app.use(bodyParser.json());

// Connect to SQLite database
const db = new sqlite3.Database(':memory:'); // In-memory database for simplicity

// Create users table
db.serialize(() => {
    db.run('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT, password TEXT)');

    // Insert some example users
    const stmt = db.prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    const salt = bcrypt.genSaltSync(10);
    const hash1 = bcrypt.hashSync('password1', salt);  //Example
    const hash2 = bcrypt.hashSync('password2', salt);  //Example
    stmt.run('user1', hash1); //Example
    stmt.run('user2', hash2); //Example
    stmt.finalize();
});

// User login endpoint
app.post('/login', (req, res) => {
    const { username, password } = req.body;

    db.get('SELECT * FROM users WHERE username = ?', [username], (err, row) => {
        if (err) {
            res.status(500).json({ message: 'Internal server error' });
        } else if (!row) {
            res.status(401).json({ message: 'Invalid credentials' });
        } else {
            const passwordHash = row.password;
            if (bcrypt.compareSync(password, passwordHash)) {
                res.json({ message: 'Login successful' });
            } else {
                res.status(401).json({ message: 'Invalid credentials' });
            }
        }
    });
});

app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
