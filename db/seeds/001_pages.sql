INSERT INTO pages (slug, title, body)
VALUES
    ('home', 'Home', 'Hello world!'),
    ('about', 'About', 'About this PHP app')
ON CONFLICT (slug) DO NOTHING;
