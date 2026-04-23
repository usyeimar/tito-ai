SELECT 'CREATE DATABASE testing_app'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'testing_app')\gexec
