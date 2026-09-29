INSERT INTO "users" (username, email)
VALUES
    ('khanriza', 'khanriza@gmail.com'),
    ('rkhan', 'rkhan@gmail.com')
ON CONFLICT (username) DO NOTHING;
