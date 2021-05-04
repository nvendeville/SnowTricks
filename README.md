# SnowTricks
Projet 6 - Développeur d'application - PHP / Symfony - OpenClassRooms

# prerequest
Composer https://getcomposer.org/download/

# install and run
- **Step 1** : Clone the Github repository

- **Step 2** : In terminal paste the URL cloned after the command ``git clone``

- **Step 3** : In Terminal run the command ``composer install``

- **Step 4** : Choose a name for your DataBase

- **Step 5** : Update ``###> doctrine/doctrine-bundle ###`` in your file **.env**

  **- Uncomment the ligne related to your SGBQ**

    DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" **for sqlite**
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name" **for mysql**
    DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=13&charset=utf8" **for postgresql**

  **- Set the db_user and/or db_password and/or db_name (name chosen on step 4)**

- **Step 5** : Update ``###> symfony/mailer ###`` in your file **.env**

  **- Uncomment the MAILER_DSN related to your SMTP server**
  
  MAILER_DSN=smtp://smtp_user:smtp_password@smtp_server:port
  
  **- Set the smtp_user, smtp_password and smtp_server:port**
  
- **Step 6** : In your terminal, create your database
  
Step 4 : from your browser go to http://locahost:8000
